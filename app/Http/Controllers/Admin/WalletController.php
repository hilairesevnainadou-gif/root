<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Liste de tous les wallets avec filtres
     */
    public function index(Request $request): View
    {
        $query = Wallet::with('user')
            ->withCount('transactions')
            ->withSum(['transactions as total_credited' => function ($q) {
                $q->where('type', 'credit')->where('status', 'completed');
            }], 'amount')
            ->withSum(['transactions as total_debited' => function ($q) {
                $q->where('type', 'debit')->where('status', 'completed');
            }], 'amount');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('wallet_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('first_name', 'like', "%{$search}%")
                                                     ->orWhere('last_name', 'like', "%{$search}%")
                                                     ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $wallets = $query->orderByDesc('balance')->paginate(20)->withQueryString();

        // Stats globales
        $stats = [
            'total_wallets'   => Wallet::count(),
            'total_balance'   => Wallet::where('status', 'active')->sum('balance'),
            'active_wallets'  => Wallet::where('status', 'active')->count(),
            'pending_withdrawals' => Transaction::where('type', 'debit')
                ->where('status', 'pending')->count(),
            'pending_amount'  => Transaction::where('type', 'debit')
                ->where('status', 'pending')->sum('amount'),
        ];

        return view('admin.wallets.index', compact('wallets', 'stats'));
    }

    /**
     * Détail d'un wallet
     */
    public function show(Wallet $wallet): View
    {
        $wallet->load('user');

        $transactions = $wallet->transactions()
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total_credited'  => $wallet->transactions()->where('type', 'credit')->where('status', 'completed')->sum('amount'),
            'total_debited'   => $wallet->transactions()->where('type', 'debit')->where('status', 'completed')->sum('amount'),
            'total_fees'      => $wallet->transactions()->where('status', 'completed')->sum('fee'),
            'pending_debit'   => $wallet->transactions()->where('type', 'debit')->where('status', 'pending')->sum('total_amount'),
            'tx_count'        => $wallet->transactions()->count(),
        ];

        return view('admin.wallets.show', compact('wallet', 'transactions', 'stats'));
    }

    /**
     * Liste des retraits en attente
     */
    public function withdrawals(Request $request): View
    {
        $query = Transaction::with(['wallet.user'])
            ->where('type', 'debit')
            ->whereIn('status', ['pending', 'processing']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_id', 'like', "%{$s}%")
                  ->orWhereHas('wallet.user', fn($u) => $u->where('email', 'like', "%{$s}%")
                                                           ->orWhere('first_name', 'like', "%{$s}%")
                                                           ->orWhere('last_name', 'like', "%{$s}%"));
            });
        }

        $withdrawals = $query->orderBy('created_at')->paginate(20)->withQueryString();

        $totals = [
            'pending_count'  => Transaction::where('type', 'debit')->where('status', 'pending')->count(),
            'pending_amount' => Transaction::where('type', 'debit')->where('status', 'pending')->sum('amount'),
        ];

        return view('admin.wallets.withdrawals', compact('withdrawals', 'totals'));
    }

    /**
     * Approuver un retrait
     */
    public function approveWithdrawal(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'debit' || $transaction->status !== 'pending') {
            return back()->with('error', 'Ce retrait ne peut pas être approuvé.');
        }

        $request->validate([
            'reference' => ['nullable', 'string', 'max:255'],
            'note'      => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($transaction, $request) {
            $transaction->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'reference'    => $request->reference,
                'metadata'     => array_merge($transaction->metadata ?? [], [
                    'approved_by'  => auth()->id(),
                    'approved_at'  => now()->toIso8601String(),
                    'admin_note'   => $request->note,
                ]),
            ]);

            // Notification utilisateur
            $transaction->wallet->user->notifications()->create([
                'type'    => 'withdrawal_approved',
                'title'   => 'Retrait approuvé',
                'message' => 'Votre retrait de ' . number_format($transaction->amount, 0, ',', ' ') . ' FCFA a été traité.',
                'is_read' => false,
                'data'    => ['transaction_id' => $transaction->transaction_id, 'amount' => $transaction->amount],
            ]);

            Log::info('Retrait approuvé par admin', [
                'transaction_id' => $transaction->transaction_id,
                'admin_id'       => auth()->id(),
                'amount'         => $transaction->amount,
            ]);
        });

        return back()->with('success', 'Retrait approuvé et marqué comme complété.');
    }

    /**
     * Rejeter un retrait → rembourser le wallet
     */
    public function rejectWithdrawal(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'debit' || $transaction->status !== 'pending') {
            return back()->with('error', 'Ce retrait ne peut pas être rejeté.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($transaction, $request) {
            // Rembourser
            $wallet = $transaction->wallet;
            $wallet->balance += $transaction->total_amount;
            $wallet->save();

            $transaction->update([
                'status'   => 'cancelled',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'rejected_by'     => auth()->id(),
                    'rejected_at'     => now()->toIso8601String(),
                    'rejection_reason'=> $request->reason,
                ]),
            ]);

            // Notification
            $wallet->user->notifications()->create([
                'type'    => 'withdrawal_rejected',
                'title'   => 'Retrait refusé',
                'message' => 'Votre demande de retrait de ' . number_format($transaction->amount, 0, ',', ' ') . ' FCFA a été refusée. Motif : ' . $request->reason,
                'is_read' => false,
                'data'    => ['transaction_id' => $transaction->transaction_id],
            ]);

            Log::info('Retrait refusé par admin', [
                'transaction_id' => $transaction->transaction_id,
                'admin_id'       => auth()->id(),
                'reason'         => $request->reason,
            ]);
        });

        return back()->with('success', 'Retrait refusé. Le solde a été recrédité sur le wallet.');
    }

    /**
     * Ajustement manuel (crédit ou débit admin)
     */
    public function adjust(Request $request, Wallet $wallet): RedirectResponse
    {
        $request->validate([
            'type'   => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:100', 'max:10000000'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $amount = (float) $request->amount;

        if ($request->type === 'debit' && $wallet->balance < $amount) {
            return back()->with('error', 'Solde insuffisant pour effectuer ce débit.');
        }

        DB::transaction(function () use ($request, $wallet, $amount) {
            $txId = 'ADJ-' . strtoupper(Str::random(10));

            Transaction::create([
                'wallet_id'      => $wallet->id,
                'transaction_id' => $txId,
                'type'           => $request->type,
                'amount'         => $amount,
                'fee'            => 0,
                'total_amount'   => $amount,
                'payment_method' => 'admin_adjustment',
                'status'         => 'completed',
                'completed_at'   => now(),
                'description'    => 'Ajustement admin : ' . $request->reason,
                'metadata'       => [
                    'admin_id'     => auth()->id(),
                    'admin_name'   => auth()->user()->full_name,
                    'reason'       => $request->reason,
                    'adjusted_at'  => now()->toIso8601String(),
                ],
            ]);

            if ($request->type === 'credit') {
                $wallet->balance += $amount;
            } else {
                $wallet->balance -= $amount;
            }
            $wallet->last_transaction_at = now();
            $wallet->save();

            // Notification
            $wallet->user->notifications()->create([
                'type'    => 'wallet_adjustment',
                'title'   => $request->type === 'credit' ? 'Crédit administrateur' : 'Débit administrateur',
                'message' => 'Votre portefeuille a été ' . ($request->type === 'credit' ? 'crédité' : 'débité') . ' de ' . number_format($amount, 0, ',', ' ') . ' FCFA.',
                'is_read' => false,
                'data'    => ['transaction_id' => $txId, 'amount' => $amount, 'type' => $request->type],
            ]);

            Log::info('Ajustement wallet admin', [
                'wallet_id'  => $wallet->id,
                'type'       => $request->type,
                'amount'     => $amount,
                'admin_id'   => auth()->id(),
                'reason'     => $request->reason,
            ]);
        });

        $label = $request->type === 'credit' ? 'Crédit' : 'Débit';
        return back()->with('success', "{$label} de " . number_format($amount, 0, ',', ' ') . ' FCFA effectué avec succès.');
    }

    /**
     * Suspendre / Réactiver un wallet
     */
    public function toggleStatus(Wallet $wallet): RedirectResponse
    {
        $newStatus = $wallet->status === 'active' ? 'suspended' : 'active';
        $wallet->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'réactivé' : 'suspendu';

        Log::info("Wallet {$label} par admin", [
            'wallet_id' => $wallet->id,
            'admin_id'  => auth()->id(),
        ]);

        return back()->with('success', "Wallet {$label} avec succès.");
    }
}
