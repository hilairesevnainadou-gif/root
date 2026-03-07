<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Afficher le portefeuille
     */
    public function show(): View
    {
        $user = auth()->user();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'wallet_number' => 'BHDM-WALLET-' . strtoupper(Str::random(8)),
                'balance' => 0,
                'currency' => 'XOF',
                'status' => 'active',
                'activated_at' => now(),
            ]
        );

        $recentTransactions = $wallet->transactions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_deposits' => $wallet->transactions()->where('type', 'credit')->where('status', 'completed')->sum('amount'),
            'total_withdrawals' => $wallet->transactions()->where('type', 'debit')->where('status', 'completed')->sum('amount'),
            'pending_transactions' => $wallet->transactions()->where('status', 'pending')->count(),
        ];

        return view('client.wallet.show', compact('wallet', 'recentTransactions', 'stats'));
    }

    /**
     * Historique des transactions
     */
    public function transactions(Request $request): View
    {
        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();

        $query = $wallet->transactions();

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20);

        return view('client.wallet.transactions', compact('transactions', 'wallet'));
    }

    /**
     * Initier un dépôt (redirection vers passerelle)
     */
    public function deposit(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'payment_method' => ['required', 'in:wave,orange_money,free_money,card,bank_transfer'],
        ]);

        $wallet = Wallet::where('user_id', auth()->id())->firstOrFail();

        // Calcul des frais (pas de taux, juste des frais fixes si applicable)
        $fee = match($request->payment_method) {
            'card' => $request->amount * 0.025,
            default => $request->amount * 0.01, // 1% pour mobile money
        };

        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'transaction_id' => (string) Str::uuid(),
            'type' => 'credit',
            'amount' => $request->amount,
            'fee' => $fee,
            'total_amount' => $request->amount - $fee,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'description' => 'Dépôt via ' . $request->payment_method,
        ]);

        // Redirection vers passerelle de paiement (Kkiapay, etc.)
        // $paymentUrl = $this->initiatePayment($transaction);
        // return redirect()->away($paymentUrl);

        return redirect()
            ->route('client.wallet.show')
            ->with('info', 'Transaction initiée. Référence: ' . $transaction->transaction_id);
    }
}
