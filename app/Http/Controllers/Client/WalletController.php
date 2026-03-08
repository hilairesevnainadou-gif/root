<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\FundingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Afficher le portefeuille (vue show)
     */
    public function show(): View
    {
        $user = auth()->user();

        // Créer le wallet s'il n'existe pas
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'wallet_number' => 'WLT-' . strtoupper(Str::random(8)),
                'balance' => 0,
                'currency' => 'XOF',
                'status' => 'active',
                'activated_at' => now(),
            ]
        );

        // Transactions récentes
        $recentTransactions = $wallet->transactions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Statistiques
        $stats = [
            'total_deposits' => $wallet->transactions()
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_withdrawals' => $wallet->transactions()
                ->where('type', 'debit')
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_transactions' => $wallet->transactions()
                ->where('status', 'pending')
                ->count(),
        ];

        return view('client.wallet.show', compact('wallet', 'recentTransactions', 'stats'));
    }

    /**
     * Historique complet des transactions
     */
    public function transactions(Request $request): View
    {
        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();

        $query = $wallet->transactions();

        // Filtres optionnels
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20);

        return view('client.wallet.transactions', compact('transactions', 'wallet'));
    }

    /**
     * Formulaire de dépôt (vue deposit)
     */
    public function depositForm(): View
    {
        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();
        
        return view('client.wallet.deposit', compact('wallet'));
    }

    /**
     * Initier un dépôt (appel AJAX depuis la vue deposit)
     */
    public function deposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000', 'max:1000000'],
            'payment_method' => ['required', 'in:kkiapay'],
        ]);

        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();

        // Calcul des frais : 1%
        $fee = round($validated['amount'] * 0.01);
        $total = $validated['amount'] + $fee;

        // Créer la transaction en pending
        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'transaction_id' => 'WLT-DEP-' . strtoupper(Str::random(10)),
            'type' => 'credit',
            'amount' => $validated['amount'],
            'fee' => $fee,
            'total_amount' => $total,
            'payment_method' => 'kkiapay',
            'status' => 'pending',
            'description' => 'Dépôt sur portefeuille',
            'metadata' => [
                'initiated_at' => now()->toIso8601String(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'transaction' => $transaction,
            'message' => 'Transaction initiée',
        ]);
    }

    /**
     * Vérifier le statut d'un dépôt (polling après paiement Kkiapay)
     */
    public function verifyDeposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transactionId' => 'required|string', // ID Kkiapay
            'internal_transaction_id' => 'required|string', // Notre ID
        ]);

        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])
            ->whereHas('wallet', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->firstOrFail();

        // Si déjà complété
        if ($transaction->status === 'completed') {
            return response()->json([
                'success' => true,
                'status' => 'completed',
                'new_balance' => $transaction->wallet->fresh()->balance,
            ]);
        }

        // Vérifier via API Kkiapay
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.kkiapay.private_key'),
                'Accept' => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/' . $validated['transactionId']);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    // Traiter le succès
                    $this->processSuccessfulDeposit($transaction, $validated['transactionId'], $data);

                    return response()->json([
                        'success' => true,
                        'status' => 'completed',
                        'new_balance' => $transaction->wallet->fresh()->balance,
                    ]);
                }

                if (isset($data['status']) && in_array($data['status'], ['failed', 'cancelled'])) {
                    $transaction->update([
                        'status' => 'failed',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'failure_reason' => $data['status'],
                        ]),
                    ]);

                    return response()->json([
                        'success' => false,
                        'status' => 'failed',
                        'message' => 'Le paiement a échoué',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur vérification dépôt Kkiapay', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);
        }

        // Toujours en attente
        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => 'Vérification en cours',
        ]);
    }

    /**
     * Traiter un dépôt réussi
     */
    private function processSuccessfulDeposit(Transaction $transaction, string $kkiapayId, array $data): void
    {
        DB::transaction(function () use ($transaction, $kkiapayId, $data) {
            // Mettre à jour la transaction
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'reference' => $kkiapayId,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $kkiapayId,
                    'kkiapay_data' => $data,
                    'completed_at' => now()->toIso8601String(),
                ]),
            ]);

            // Créditer le wallet
            $wallet = $transaction->wallet;
            $wallet->balance += $transaction->amount;
            $wallet->last_transaction_at = now();
            $wallet->save();

            // Créer notification
            auth()->user()->notifications()->create([
                'type' => 'wallet_deposit',
                'title' => 'Dépôt réussi',
                'message' => 'Votre portefeuille a été crédité de ' . number_format($transaction->amount, 0, ',', ' ') . ' FCFA',
                'is_read' => false,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                ],
            ]);

            Log::info('Dépôt wallet complété', [
                'transaction_id' => $transaction->transaction_id,
                'amount' => $transaction->amount,
                'new_balance' => $wallet->balance,
            ]);
        });
    }

    /**
     * Webhook Kkiapay pour les dépôts wallet
     */
    public function webhook(Request $request): JsonResponse
    {
        Log::channel('kkiapay')->info('Webhook wallet dépôt reçu', $request->all());

        // Vérification signature
        $receivedSecret = $request->header('x-kkiapay-secret');
        if ($receivedSecret !== config('services.kkiapay.webhook_secret')) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $transactionId = $request->input('transactionId');
        $isSuccess = $request->boolean('isPaymentSucces');

        if (!$transactionId) {
            return response()->json(['error' => 'Missing transactionId'], 400);
        }

        try {
            // Chercher par référence ou dans metadata
            $transaction = Transaction::where('reference', $transactionId)
                ->orWhereJsonContains('metadata', ['kkiapay_transaction_id' => $transactionId])
                ->where('status', 'pending')
                ->first();

            if (!$transaction) {
                Log::warning('Transaction wallet non trouvée', ['kkiapay_id' => $transactionId]);
                return response()->json(['status' => 'not_found'], 200);
            }

            if ($isSuccess && $request->input('event') === 'transaction.success') {
                $this->processSuccessfulDeposit($transaction, $transactionId, $request->all());
            } else {
                $transaction->update(['status' => 'failed']);
            }

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            Log::error('Erreur webhook wallet', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Formulaire de retrait (vue withdraw)
     */
    public function withdrawForm(): View|RedirectResponse
    {
        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();

        // Vérifier solde minimum
        if ($wallet->balance < 5000) {
            return redirect()
                ->route('client.wallet.show')
                ->with('error', 'Solde insuffisant. Minimum 5 000 FCFA requis pour un retrait.');
        }

        return view('client.wallet.withdraw', compact('wallet'));
    }

    /**
     * Soumettre une demande de retrait
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:5000'],
            'method' => ['required', 'in:mobile_money,bank_transfer'],
            'payment_details' => ['required', 'array'],
            'payment_details.operator' => ['required_if:method,mobile_money', 'string'],
            'payment_details.number' => ['required_if:method,mobile_money', 'string', 'min:10'],
            'payment_details.account_name' => ['required_if:method,bank_transfer', 'string'],
            'payment_details.account_number' => ['required_if:method,bank_transfer', 'string'],
            'payment_details.bank_name' => ['required_if:method,bank_transfer', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();

        // Vérifier solde suffisant
        $fee = max(round($validated['amount'] * 0.02), 500); // 2% min 500 FCFA
        $totalDebit = $validated['amount'] + $fee;

        if ($wallet->balance < $totalDebit) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant. Montant + frais = ' . number_format($totalDebit, 0, ',', ' ') . ' FCFA',
            ], 422);
        }

        // Vérifier retraits en attente
        $pendingWithdrawals = $wallet->transactions()
            ->where('type', 'debit')
            ->where('status', 'pending')
            ->count();

        if ($pendingWithdrawals >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà 3 demandes de retrait en attente.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Créer la transaction de retrait
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => 'WLT-WDR-' . strtoupper(Str::random(10)),
                'type' => 'debit',
                'amount' => $validated['amount'],
                'fee' => $fee,
                'total_amount' => $totalDebit,
                'payment_method' => $validated['method'],
                'status' => 'pending', // En attente validation admin
                'description' => 'Demande de retrait - ' . ($validated['reason'] ?? 'Sans motif'),
                'metadata' => [
                    'payment_details' => $validated['payment_details'],
                    'requested_at' => now()->toIso8601String(),
                    'reason' => $validated['reason'],
                ],
            ]);

            // Débiter immédiatement le wallet (réservation)
            $wallet->balance -= $totalDebit;
            $wallet->last_transaction_at = now();
            $wallet->save();

            // Notification admin (optionnel)
            // event(new WithdrawalRequested($transaction));

            DB::commit();

            return response()->json([
                'success' => true,
                'reference' => $transaction->transaction_id,
                'amount' => $validated['amount'],
                'fee' => $fee,
                'net_amount' => $validated['amount'] - $fee,
                'message' => 'Demande de retrait soumise avec succès',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création retrait', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la demande',
            ], 500);
        }
    }

    /**
     * Annuler une demande de retrait en attente
     */
    public function cancelWithdrawal(Transaction $transaction): RedirectResponse
    {
        // Vérifier propriétaire
        if ($transaction->wallet->user_id !== auth()->id()) {
            abort(403);
        }

        // Vérifier statut
        if ($transaction->status !== 'pending' || $transaction->type !== 'debit') {
            return back()->with('error', 'Cette transaction ne peut pas être annulée.');
        }

        DB::transaction(function () use ($transaction) {
            // Rembourser le wallet
            $wallet = $transaction->wallet;
            $wallet->balance += $transaction->total_amount;
            $wallet->save();

            // Annuler la transaction
            $transaction->update([
                'status' => 'cancelled',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'cancelled_at' => now()->toIso8601String(),
                    'cancelled_by' => auth()->id(),
                ]),
            ]);

            // Notification
            auth()->user()->notifications()->create([
                'type' => 'wallet_withdrawal_cancelled',
                'title' => 'Retrait annulé',
                'message' => 'Votre demande de retrait a été annulée. Les fonds ont été recrédités.',
                'is_read' => false,
            ]);
        });

        return back()->with('success', 'Demande de retrait annulée. Les fonds ont été recrédités sur votre portefeuille.');
    }
}