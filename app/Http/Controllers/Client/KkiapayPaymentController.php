<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FundingRequest;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse;

class KkiapayPaymentController extends Controller
{
    private string $publicKey;
    private string $privateKey;
    private string $webhookSecret;
    private bool   $sandbox;

    public function __construct()
    {
        $this->sandbox        = config('services.kkiapay.sandbox', true);
        $this->publicKey      = config('services.kkiapay.public_key');
        $this->privateKey     = config('services.kkiapay.private_key');
        $this->webhookSecret  = config('services.kkiapay.webhook_secret', '');
    }

    // =========================================================================
    //  INITIALIZE
    // =========================================================================

    /**
     * Point d'entrée : frais d'inscription OU dépôt wallet
     * Le paramètre `type` du body peut valoir 'final_fee' pour les frais de dossier.
     */
    public function initialize(Request $request, ?FundingRequest $fundingRequest = null): JsonResponse
    {
        $type = $request->input('type', 'registration_fee');

        Log::channel('kkiapay')->info('=== INITIALIZE PAYMENT ===', [
            'funding_request_id' => $fundingRequest?->id,
            'user_id'            => auth()->id(),
            'type'               => $type,
        ]);

        if ($fundingRequest) {
            if ($type === 'final_fee') {
                return $this->initializeFinalPayment($fundingRequest);
            }
            return $this->initializeRegistrationPayment($fundingRequest);
        }

        return $this->initializeWalletDeposit();
    }

    /**
     * Initialiser le paiement des frais d'inscription (status draft / payment_status pending).
     */
    private function initializeRegistrationPayment(FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $fundingRequest->loadMissing('typeFinancement');

        if ($fundingRequest->isPaid()) {
            $completedTx = Transaction::where('funding_request_id', $fundingRequest->id)
                ->where('status', 'completed')
                ->latest()
                ->first();

            if ($completedTx) {
                return response()->json([
                    'success'      => true,
                    'already_paid' => true,
                    'redirect_url' => $this->getRedirectUrl($fundingRequest),
                    'message'      => 'Paiement déjà confirmé.',
                ]);
            }
        }

        if (! ($fundingRequest->status === 'draft' && $fundingRequest->payment_status === 'pending')) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne peut pas être payée (statut : '.$fundingRequest->status.').',
            ], 400);
        }

        $typeFinancement = $fundingRequest->typeFinancement;
        if (! $typeFinancement) {
            return response()->json(['success' => false, 'message' => 'Type de financement introuvable'], 400);
        }

        $amount = (float) $typeFinancement->registration_fee;
        $wallet = $this->getOrCreateWallet();

        $existingTx = Transaction::where('funding_request_id', $fundingRequest->id)
            ->where('status', 'pending')
            ->where('type', 'payment')
            ->latest()
            ->first();

        if ($existingTx) {
            return $this->buildInitializeResponse($existingTx, $amount, $fundingRequest);
        }

        $transaction = Transaction::create([
            'wallet_id'          => $wallet->id,
            'funding_request_id' => $fundingRequest->id,
            'transaction_id'     => 'TXN-' . uniqid() . '-' . time(),
            'type'               => 'payment',
            'amount'             => $amount,
            'fee'                => 0,
            'total_amount'       => $amount,
            'payment_method'     => 'kkiapay',
            'status'             => 'pending',
            'description'        => "Frais d'inscription — {$fundingRequest->request_number}",
            'metadata'           => [
                'funding_request_id'     => $fundingRequest->id,
                'type'                   => 'registration_fee',
                'user_id'                => auth()->id(),
                'kkiapay_initialized_at' => now()->toIso8601String(),
            ],
        ]);

        return $this->buildInitializeResponse($transaction, $amount, $fundingRequest);
    }

    /**
     * Initialiser le paiement des frais de dossier finals (status approved).
     */
    private function initializeFinalPayment(FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $fundingRequest->loadMissing('typeFinancement');

        if ($fundingRequest->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'La demande doit être approuvée pour régler les frais de dossier.',
            ], 400);
        }

        if ($fundingRequest->final_fee_paid ?? false) {
            return response()->json([
                'success'      => true,
                'already_paid' => true,
                'redirect_url' => route('client.requests.show', $fundingRequest),
                'message'      => 'Frais de dossier déjà réglés.',
            ]);
        }

        $finalFee = (float) ($fundingRequest->typeFinancement->registration_final_fee ?? 0);
        if ($finalFee <= 0) {
            return response()->json(['success' => false, 'message' => 'Aucun frais de dossier configuré.'], 400);
        }

        $wallet = $this->getOrCreateWallet();

        $existingTx = Transaction::where('funding_request_id', $fundingRequest->id)
            ->where('status', 'pending')
            ->where('type', 'payment')
            ->whereJsonContains('metadata', ['type' => 'final_fee'])
            ->latest()
            ->first();

        if ($existingTx) {
            return $this->buildInitializeResponse($existingTx, $finalFee, $fundingRequest);
        }

        $transaction = Transaction::create([
            'wallet_id'          => $wallet->id,
            'funding_request_id' => $fundingRequest->id,
            'transaction_id'     => 'TXN-FINAL-' . uniqid() . '-' . time(),
            'type'               => 'payment',
            'amount'             => $finalFee,
            'fee'                => 0,
            'total_amount'       => $finalFee,
            'payment_method'     => 'kkiapay',
            'status'             => 'pending',
            'description'        => "Frais de dossier — {$fundingRequest->request_number}",
            'metadata'           => [
                'funding_request_id'     => $fundingRequest->id,
                'type'                   => 'final_fee',
                'user_id'                => auth()->id(),
                'kkiapay_initialized_at' => now()->toIso8601String(),
            ],
        ]);

        return $this->buildInitializeResponse($transaction, $finalFee, $fundingRequest);
    }

    /**
     * Initialiser un dépôt wallet
     */
    public function initializeWalletDeposit(): JsonResponse
    {
        $amount = (float) request()->input('amount', 0);

        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Montant invalide'], 400);
        }

        $wallet = $this->getOrCreateWallet();

        $transaction = Transaction::create([
            'wallet_id'          => $wallet->id,
            'funding_request_id' => null,
            'transaction_id'     => 'WLT-DEP-' . strtoupper(uniqid()) . '-' . time(),
            'type'               => 'credit',
            'amount'             => $amount,
            'fee'                => 0,
            'total_amount'       => $amount,
            'payment_method'     => 'kkiapay',
            'status'             => 'pending',
            'description'        => 'Dépôt wallet — ' . number_format($amount, 0, ',', ' ') . ' FCFA',
            'metadata'           => [
                'type'                   => 'wallet_deposit',
                'amount_credited'        => $amount,
                'user_id'                => auth()->id(),
                'kkiapay_initialized_at' => now()->toIso8601String(),
            ],
        ]);

        return $this->buildInitializeResponse($transaction, $amount, null);
    }

    // =========================================================================
    //  WALLET DIRECT PAYMENT
    // =========================================================================

    /**
     * Payer les frais d'inscription directement depuis le wallet.
     * ➜ Débite le wallet, marque la demande comme soumise.
     *    Le versement du financement n'a lieu qu'après validation admin.
     */
    public function payWithWallet(Request $request, FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $fundingRequest->loadMissing('typeFinancement');

        if ($fundingRequest->isPaid()) {
            return response()->json([
                'success'      => true,
                'already_paid' => true,
                'redirect_url' => $this->getRedirectUrl($fundingRequest),
            ]);
        }

        if (! ($fundingRequest->status === 'draft' && $fundingRequest->payment_status === 'pending')) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne peut pas être payée.',
            ], 400);
        }

        $amount = (float) $fundingRequest->typeFinancement->registration_fee;
        $wallet = $this->getOrCreateWallet();

        if ((float) $wallet->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Solde wallet insuffisant. Solde : ' . number_format($wallet->balance, 0, ',', ' ') . ' FCFA.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($fundingRequest, $wallet, $amount) {
                $wallet->decrement('balance', $amount);
                $wallet->update(['last_transaction_at' => now()]);

                $txId = 'TXN-WALLET-' . uniqid() . '-' . time();

                Transaction::create([
                    'wallet_id'          => $wallet->id,
                    'funding_request_id' => $fundingRequest->id,
                    'transaction_id'     => $txId,
                    'type'               => 'payment',
                    'amount'             => $amount,
                    'fee'                => 0,
                    'total_amount'       => $amount,
                    'payment_method'     => 'wallet',   // ← valeur maintenant dans l'ENUM
                    'status'             => 'completed',
                    'completed_at'       => now(),
                    'reference'          => $txId,
                    'description'        => "Frais d'inscription — {$fundingRequest->request_number}",
                    'metadata'           => [
                        'type'                  => 'registration_fee',
                        'payment_method'        => 'wallet',
                        'wallet_id'             => $wallet->id,
                        'wallet_balance_before' => $wallet->balance + $amount,
                        'processed_at'          => now()->toIso8601String(),
                    ],
                ]);

                // Marquer la demande comme soumise (pas encore financée)
                $fundingRequest->markAsPaid($txId, $amount);
            });

            $fundingRequest->refresh();

            Log::channel('kkiapay')->info('✅ Paiement wallet inscription réussi', [
                'funding_request_id' => $fundingRequest->id,
                'amount'             => $amount,
                'wallet_id'          => $wallet->id,
            ]);

            return response()->json([
                'success'      => true,
                'redirect_url' => $this->getRedirectUrl($fundingRequest),
                'message'      => 'Paiement effectué avec succès.',
            ]);

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('❌ Erreur paiement wallet inscription', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du paiement : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Payer les frais de dossier finals directement depuis le wallet.
     *
     * CORRECTION IMPORTANTE :
     *  - On DÉBITE les frais du wallet du client (c'est le règlement des frais).
     *  - On marque final_fee_paid = true et status = 'pending_disbursement'
     *    pour indiquer à l'admin que le versement est à valider.
     *  - Le versement du montant net sur le wallet n'est PAS fait ici :
     *    c'est l'admin qui déclenche disburseToWallet() depuis le back-office.
     */
    public function payFinalWithWallet(Request $request, FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $fundingRequest->loadMissing('typeFinancement');

        if ($fundingRequest->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'La demande doit être approuvée pour régler les frais de dossier.',
            ], 400);
        }

        if ($fundingRequest->final_fee_paid ?? false) {
            return response()->json([
                'success'      => true,
                'already_paid' => true,
                'redirect_url' => route('client.requests.show', $fundingRequest),
            ]);
        }

        $finalFee = (float) ($fundingRequest->typeFinancement->registration_final_fee ?? 0);
        if ($finalFee <= 0) {
            return response()->json(['success' => false, 'message' => 'Aucun frais de dossier configuré.'], 400);
        }

        $wallet = $this->getOrCreateWallet();

        if ((float) $wallet->balance < $finalFee) {
            return response()->json([
                'success' => false,
                'message' => 'Solde wallet insuffisant. Solde : ' . number_format($wallet->balance, 0, ',', ' ') . ' FCFA.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($fundingRequest, $wallet, $finalFee) {
                // Débiter les frais du wallet client
                $wallet->decrement('balance', $finalFee);
                $wallet->update(['last_transaction_at' => now()]);

                $txId = 'TXN-FINAL-WALLET-' . uniqid() . '-' . time();

                Transaction::create([
                    'wallet_id'          => $wallet->id,
                    'funding_request_id' => $fundingRequest->id,
                    'transaction_id'     => $txId,
                    'type'               => 'payment',
                    'amount'             => $finalFee,
                    'fee'                => 0,
                    'total_amount'       => $finalFee,
                    'payment_method'     => 'wallet',   // ← valeur maintenant dans l'ENUM
                    'status'             => 'completed',
                    'completed_at'       => now(),
                    'reference'          => $txId,
                    'description'        => "Frais de dossier — {$fundingRequest->request_number}",
                    'metadata'           => [
                        'type'                  => 'final_fee',
                        'payment_method'        => 'wallet',
                        'wallet_id'             => $wallet->id,
                        'wallet_balance_before' => $wallet->balance + $finalFee,
                        'processed_at'          => now()->toIso8601String(),
                    ],
                ]);

                /*
                 * On marque les frais comme payés et on passe en
                 * 'pending_disbursement' : l'admin verra que les frais sont
                 * réglés et pourra déclencher le versement du financement.
                 * On ne crédite PAS le wallet ici → c'est le rôle de
                 * FundingRequestController::disburseToWallet().
                 */
                $fundingRequest->update([
                    'final_fee_paid'    => true,
                    'final_fee_paid_at' => now(),
                    'status'            => 'pending_disbursement',
                ]);
            });

            // Notifier l'admin qu'il doit valider le versement
            \App\Models\Notification::create([
                'user_id' => $fundingRequest->user_id,
                'type'    => 'final_fee_paid_pending_disbursement',
                'title'   => 'Frais de dossier réglés — versement en attente',
                'message' => "Les frais de dossier de votre demande #{$fundingRequest->request_number} ont été réglés. "
                           . "Le versement de votre financement sera effectué après validation de notre équipe.",
                'data'    => [
                    'funding_request_id' => $fundingRequest->id,
                    'final_fee'          => $finalFee,
                ],
            ]);

            Log::channel('kkiapay')->info('✅ Paiement wallet frais finals réussi — en attente de versement admin', [
                'funding_request_id' => $fundingRequest->id,
                'amount'             => $finalFee,
            ]);

            return response()->json([
                'success'      => true,
                'redirect_url' => route('client.requests.show', $fundingRequest),
                'message'      => 'Frais de dossier réglés avec succès. Le versement sera effectué après validation.',
            ]);

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('❌ Erreur paiement wallet frais finals', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du paiement : ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    //  VERIFY (Kkiapay SDK callback)
    // =========================================================================

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transactionId'          => 'required|string',
            'funding_request_id'     => 'nullable|integer|exists:funding_requests,id',
            'internal_transaction_id'=> 'required|string',
        ]);

        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])
            ->where('status', 'pending')
            ->first();

        if (! $transaction) {
            $completedTx = Transaction::where('transaction_id', $validated['internal_transaction_id'])
                ->where('status', 'completed')
                ->first();

            if ($completedTx) {
                return $this->handleCompletedTransaction($completedTx, $validated);
            }

            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        if ($transaction->wallet->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        if (empty($transaction->reference) || $transaction->reference !== $validated['transactionId']) {
            $transaction->update(['reference' => $validated['transactionId']]);
        }

        if ($transaction->type === 'credit') {
            return $this->verifyWalletDeposit($transaction, $validated['transactionId']);
        }

        if ((($transaction->metadata['type'] ?? '') === 'final_fee')) {
            return $this->verifyFinalFeePayment($transaction, $validated);
        }

        if (! empty($validated['funding_request_id'])) {
            return $this->verifyRegistrationPayment($transaction, $validated);
        }

        return response()->json(['success' => false, 'message' => 'Type de transaction invalide'], 400);
    }

    public function verifyFinalPayment(Request $request, FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'transactionId'           => 'required|string',
            'internal_transaction_id' => 'required|string',
            'funding_request_id'      => 'nullable|integer',
        ]);

        $fresh = $fundingRequest->fresh();
        if ($fresh->final_fee_paid ?? false) {
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => route('client.requests.show', $fresh),
            ]);
        }

        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])->first();
        if (! $transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        if (! empty($transaction->reference) && $transaction->reference !== $validated['transactionId']) {
            $transaction->update(['reference' => $validated['transactionId']]);
        }

        return $this->verifyFinalFeePayment($transaction, $validated);
    }

    // =========================================================================
    //  RECHECK
    // =========================================================================

    public function recheckPayment(Request $request, FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate(['transactionId' => 'required|string']);
        $kkiapayId = $validated['transactionId'];

        $fresh = $fundingRequest->fresh();
        if ($fresh->isPaid()) {
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => $this->getRedirectUrl($fresh),
                'message'      => 'Paiement déjà confirmé.',
            ]);
        }

        $transaction = Transaction::where('reference', $kkiapayId)
            ->where('funding_request_id', $fundingRequest->id)
            ->first();

        if ($transaction && $transaction->status === 'completed') {
            if (! $fresh->isPaid()) {
                $fresh->markAsPaid($kkiapayId, $transaction->amount);
            }
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => $this->getRedirectUrl($fresh->fresh()),
            ]);
        }

        return $this->recheckViaKkiapayApi($kkiapayId, $fundingRequest, 'registration');
    }

    public function recheckFinalPayment(Request $request, FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate(['transactionId' => 'required|string']);
        $kkiapayId = $validated['transactionId'];

        $fresh = $fundingRequest->fresh();
        if ($fresh->final_fee_paid ?? false) {
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => route('client.requests.show', $fresh),
                'message'      => 'Frais déjà confirmés.',
            ]);
        }

        $transaction = Transaction::where('reference', $kkiapayId)
            ->where('funding_request_id', $fundingRequest->id)
            ->where('type', 'payment')
            ->whereJsonContains('metadata', ['type' => 'final_fee'])
            ->first();

        if ($transaction && $transaction->status === 'completed') {
            if (! ($fresh->final_fee_paid ?? false)) {
                $fresh->update([
                    'final_fee_paid'    => true,
                    'final_fee_paid_at' => now(),
                    'status'            => 'pending_disbursement',
                ]);
            }
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => route('client.requests.show', $fresh->fresh()),
            ]);
        }

        return $this->recheckViaKkiapayApi($kkiapayId, $fundingRequest, 'final_fee');
    }

    private function recheckViaKkiapayApi(
        string        $kkiapayId,
        FundingRequest $fundingRequest,
        string        $feeType = 'registration'
    ): JsonResponse {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Accept'        => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/' . $kkiapayId);

            if ($response->successful()) {
                $data   = $response->json();
                $status = $data['status'] ?? null;

                if ($status === 'success') {
                    $amount = (float) ($data['amount'] ?? 0);

                    DB::transaction(function () use ($kkiapayId, $fundingRequest, $feeType, $amount, $data) {
                        $wallet = $this->getOrCreateWallet();

                        $tx = Transaction::where('funding_request_id', $fundingRequest->id)
                            ->where('type', 'payment')
                            ->whereIn('status', ['pending', 'failed'])
                            ->latest()
                            ->first();

                        if (! $tx) {
                            $tx = Transaction::create([
                                'wallet_id'          => $wallet->id,
                                'funding_request_id' => $fundingRequest->id,
                                'transaction_id'     => 'TXN-RECHECK-' . uniqid(),
                                'type'               => 'payment',
                                'amount'             => $amount,
                                'fee'                => $data['fees'] ?? 0,
                                'total_amount'       => $amount,
                                'payment_method'     => 'kkiapay',
                                'status'             => 'pending',
                                'description'        => ($feeType === 'final_fee' ? 'Frais de dossier' : "Frais d'inscription") . " — {$fundingRequest->request_number} (recheck)",
                                'metadata'           => ['recheck' => true, 'type' => $feeType],
                            ]);
                        }

                        $tx->update([
                            'status'       => 'completed',
                            'completed_at' => now(),
                            'reference'    => $kkiapayId,
                            'fee'          => $data['fees'] ?? 0,
                            'metadata'     => array_merge($tx->metadata ?? [], [
                                'kkiapay_transaction_id' => $kkiapayId,
                                'kkiapay_event'          => 'transaction.success',
                                'recheck_processed_at'   => now()->toIso8601String(),
                            ]),
                        ]);

                        if ($feeType === 'final_fee') {
                            $fundingRequest->update([
                                'final_fee_paid'    => true,
                                'final_fee_paid_at' => now(),
                                'status'            => 'pending_disbursement',
                            ]);
                        } else {
                            $fundingRequest->markAsPaid($kkiapayId, $amount);
                        }
                    });

                    $redirectUrl = $feeType === 'final_fee'
                        ? route('client.requests.show', $fundingRequest)
                        : $this->getRedirectUrl($fundingRequest->fresh());

                    return response()->json([
                        'success'      => true,
                        'status'       => 'paid',
                        'redirect_url' => $redirectUrl,
                        'message'      => 'Paiement retrouvé et confirmé.',
                    ]);
                }

                if (in_array($status, ['failed', 'cancelled'])) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'not_found',
                        'message' => 'Ce paiement a échoué ou a été annulé chez Kkiapay.',
                    ]);
                }

                if ($status === 'pending') {
                    return response()->json([
                        'success' => false,
                        'status'  => 'pending',
                        'message' => 'Ce paiement est encore en attente de confirmation par l\'opérateur.',
                    ]);
                }
            }

            if ($response->status() === 404) {
                return response()->json([
                    'success' => false,
                    'status'  => 'not_found',
                    'message' => 'Aucun paiement trouvé pour cet identifiant.',
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('Erreur API recheck', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => false,
            'status'  => 'error',
            'message' => 'Impossible de vérifier ce paiement pour le moment.',
        ]);
    }

    // =========================================================================
    //  VERIFY helpers
    // =========================================================================

    public function verifyDeposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transactionId'           => 'required|string',
            'internal_transaction_id' => 'required|string',
        ]);

        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->first();

        if (! $transaction || $transaction->wallet->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Transaction invalide'], 404);
        }

        return $this->verifyWalletDeposit($transaction, $validated['transactionId']);
    }

    private function handleCompletedTransaction(Transaction $transaction, array $validated): JsonResponse
    {
        if ($transaction->type === 'credit') {
            return response()->json([
                'success'      => true,
                'status'       => 'completed',
                'redirect_url' => route('client.wallet.show'),
                'message'      => 'Dépôt déjà traité.',
            ]);
        }

        if ((($transaction->metadata['type'] ?? '') === 'final_fee') && $transaction->funding_request_id) {
            $fr = FundingRequest::find($transaction->funding_request_id);
            if ($fr) {
                return response()->json([
                    'success'      => true,
                    'status'       => 'paid',
                    'redirect_url' => route('client.requests.show', $fr),
                ]);
            }
        }

        if ($transaction->funding_request_id) {
            $fr = FundingRequest::find($transaction->funding_request_id);
            if ($fr) {
                return response()->json([
                    'success'      => true,
                    'status'       => 'paid',
                    'redirect_url' => $this->getRedirectUrl($fr),
                ]);
            }
        }

        return response()->json(['success' => true, 'status' => 'completed']);
    }

    private function verifyWalletDeposit(Transaction $transaction, string $kkiapayId): JsonResponse
    {
        if ($transaction->status === 'completed') {
            return response()->json([
                'success'      => true,
                'status'       => 'completed',
                'redirect_url' => route('client.wallet.show'),
                'message'      => 'Dépôt déjà traité.',
            ]);
        }

        if ($transaction->status === 'failed') {
            return response()->json(['success' => false, 'status' => 'failed', 'message' => 'Le dépôt a échoué.']);
        }

        return $this->verifyDepositViaApi($transaction, $kkiapayId);
    }

    private function verifyRegistrationPayment(Transaction $transaction, array $validated): JsonResponse
    {
        if (empty($validated['funding_request_id'])) {
            return response()->json(['success' => false, 'message' => 'funding_request_id manquant'], 400);
        }

        $fundingRequest = FundingRequest::findOrFail($validated['funding_request_id']);

        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $freshRequest = $fundingRequest->fresh();

        if ($freshRequest->isPaid()) {
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => $this->getRedirectUrl($freshRequest),
            ]);
        }

        if ($transaction->status === 'completed') {
            if (! $freshRequest->isPaid()) {
                $freshRequest->markAsPaid($validated['transactionId'], $transaction->amount);
            }
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => $this->getRedirectUrl($freshRequest->fresh()),
            ]);
        }

        if ($transaction->status === 'failed') {
            return response()->json(['success' => false, 'status' => 'failed', 'message' => 'Le paiement a échoué.']);
        }

        return $this->verifyViaApi($transaction, $freshRequest, $validated['transactionId']);
    }

    private function verifyFinalFeePayment(Transaction $transaction, array $validated): JsonResponse
    {
        $fundingRequest = FundingRequest::find($transaction->funding_request_id);
        if (! $fundingRequest) {
            return response()->json(['success' => false, 'message' => 'Demande introuvable'], 404);
        }

        $fresh = $fundingRequest->fresh();

        if ($fresh->final_fee_paid ?? false) {
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => route('client.requests.show', $fresh),
            ]);
        }

        if ($transaction->status === 'completed') {
            if (! ($fresh->final_fee_paid ?? false)) {
                $fresh->update([
                    'final_fee_paid'    => true,
                    'final_fee_paid_at' => now(),
                    'status'            => 'pending_disbursement',
                ]);
            }
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => route('client.requests.show', $fresh->fresh()),
            ]);
        }

        if ($transaction->status === 'failed') {
            return response()->json(['success' => false, 'status' => 'failed', 'message' => 'Le paiement a échoué.']);
        }

        return $this->verifyFinalFeeViaApi($transaction, $fresh, $validated['transactionId']);
    }

    // =========================================================================
    //  API KKIAPAY verification
    // =========================================================================

    private function verifyDepositViaApi(Transaction $transaction, string $kkiapayId): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Accept'        => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/' . $kkiapayId);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    $this->processSuccessfulDeposit($transaction, $kkiapayId, $data);
                    return response()->json([
                        'success'      => true,
                        'status'       => 'completed',
                        'redirect_url' => route('client.wallet.show'),
                        'new_balance'  => $transaction->wallet->fresh()->balance,
                    ]);
                }

                if (isset($data['status']) && in_array($data['status'], ['failed', 'cancelled'])) {
                    $transaction->markAsFailed($data['status']);
                    return response()->json(['success' => false, 'status' => 'failed', 'message' => 'Le dépôt a échoué.']);
                }
            }
        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('API deposit verification error', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'status' => 'pending', 'message' => 'Vérification en cours...']);
    }

    private function verifyViaApi(Transaction $transaction, FundingRequest $fundingRequest, string $kkiapayId): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Accept'        => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/' . $kkiapayId);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    $this->processSuccessfulPayment($transaction, $fundingRequest, $kkiapayId, $data);
                    return response()->json([
                        'success'      => true,
                        'status'       => 'paid',
                        'redirect_url' => $this->getRedirectUrl($fundingRequest->fresh()),
                    ]);
                }

                if (isset($data['status']) && in_array($data['status'], ['failed', 'cancelled'])) {
                    $transaction->markAsFailed($data['status']);
                    $fundingRequest->update(['payment_status' => 'failed']);
                    return response()->json(['success' => false, 'status' => 'failed', 'message' => 'Le paiement a échoué.']);
                }
            }
        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('API registration verification error', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'status' => 'pending', 'message' => 'Vérification en cours...']);
    }

    private function verifyFinalFeeViaApi(Transaction $transaction, FundingRequest $fundingRequest, string $kkiapayId): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Accept'        => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/' . $kkiapayId);

            if ($response->successful()) {
                $data   = $response->json();
                $status = $data['status'] ?? null;

                if ($status === 'success') {
                    DB::transaction(function () use ($transaction, $fundingRequest, $kkiapayId, $data) {
                        $transaction->update([
                            'status'       => 'completed',
                            'completed_at' => now(),
                            'fee'          => $data['fees'] ?? 0,
                            'reference'    => $kkiapayId,
                            'metadata'     => array_merge($transaction->metadata ?? [], [
                                'kkiapay_transaction_id' => $kkiapayId,
                                'kkiapay_event'          => 'transaction.success',
                                'processed_at'           => now()->toIso8601String(),
                            ]),
                        ]);

                        $fundingRequest->update([
                            'final_fee_paid'    => true,
                            'final_fee_paid_at' => now(),
                            'status'            => 'pending_disbursement',
                        ]);
                    });

                    return response()->json([
                        'success'      => true,
                        'status'       => 'paid',
                        'redirect_url' => route('client.requests.show', $fundingRequest->fresh()),
                    ]);
                }

                if (in_array($status, ['failed', 'cancelled'])) {
                    $transaction->markAsFailed($status);
                    return response()->json(['success' => false, 'status' => 'failed', 'message' => 'Le paiement a échoué.']);
                }
            }
        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('API final fee verification error', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'status' => 'pending', 'message' => 'Vérification en cours...']);
    }

    // =========================================================================
    //  PROCESS SUCCESSFUL (helpers DB)
    // =========================================================================

    private function processSuccessfulDeposit(Transaction $transaction, string $transactionId, array $data): void
    {
        DB::transaction(function () use ($transaction, $transactionId, $data) {
            $actualFee    = $data['fees']   ?? 0;
            $actualAmount = $data['amount'] ?? $transaction->amount;

            $transaction->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'fee'          => $actualFee,
                'total_amount' => $actualAmount + $actualFee,
                'reference'    => $transactionId,
                'metadata'     => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event'          => 'transaction.success',
                    'kkiapay_fees'           => $actualFee,
                    'processed_at'           => now()->toIso8601String(),
                ]),
            ]);

            $wallet = $transaction->wallet;
            $wallet->balance += $transaction->amount;
            $wallet->save();
        });
    }

    private function processSuccessfulPayment(Transaction $transaction, FundingRequest $fundingRequest, string $transactionId, array $data): void
    {
        $transaction->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'fee'          => $data['fees'] ?? 0,
            'reference'    => $transactionId,
            'metadata'     => array_merge($transaction->metadata ?? [], [
                'kkiapay_transaction_id' => $transactionId,
                'kkiapay_event'          => 'transaction.success',
                'kkiapay_method'         => $data['method'] ?? null,
                'kkiapay_account'        => $data['account'] ?? null,
                'processed_at'           => now()->toIso8601String(),
            ]),
        ]);

        $fundingRequest->markAsPaid($transactionId, $data['amount'] ?? $transaction->amount);
    }

    // =========================================================================
    //  LEGACY verifyPayment
    // =========================================================================

    public function verifyPayment(Request $request, FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'transactionId' => 'required|string',
            'amount_paid'   => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($fundingRequest, $validated) {
                $fundingRequest->update([
                    'status'               => 'submitted',
                    'payment_status'       => 'paid',
                    'registration_fee_paid'=> $validated['amount_paid'],
                    'payment_reference'    => $validated['transactionId'],
                    'paid_at'              => now(),
                    'submitted_at'         => now(),
                ]);

                Transaction::create([
                    'wallet_id'          => $this->getOrCreateWallet()->id,
                    'funding_request_id' => $fundingRequest->id,
                    'transaction_id'     => 'TXN-LEGACY-' . uniqid(),
                    'type'               => 'payment',
                    'amount'             => $validated['amount_paid'],
                    'fee'                => 0,
                    'total_amount'       => $validated['amount_paid'],
                    'payment_method'     => 'kkiapay',
                    'reference'          => $validated['transactionId'],
                    'status'             => 'completed',
                    'description'        => "Frais d'inscription — {$fundingRequest->request_number}",
                    'completed_at'       => now(),
                ]);
            });

            return response()->json([
                'success'      => true,
                'redirect_url' => $this->getRedirectUrl($fundingRequest->fresh()),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors du traitement'], 500);
        }
    }

    // =========================================================================
    //  WEBHOOKS
    // =========================================================================

    public function webhook(Request $request): JsonResponse
    {
        Log::channel('kkiapay')->info('=== WEBHOOK KKIAPAY REÇU ===', [
            'ip'      => $request->ip(),
            'payload' => $request->all(),
        ]);

        if ($this->webhookSecret) {
            $received = $request->header('x-kkiapay-secret');
            if ($received !== $this->webhookSecret) {
                Log::channel('kkiapay')->error('⛔ SIGNATURE INVALIDE');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $transactionId  = $request->input('transactionId');
        $isSuccess      = $request->boolean('isPaymentSucces');
        $event          = $request->input('event');
        $amount         = (float) $request->input('amount', 0);
        $fees           = (float) $request->input('fees', 0);
        $method         = $request->input('paymentMethod');
        $account        = $request->input('account');
        $performedAt    = $request->input('performedAt');
        $stateData      = $request->input('data');
        $failureCode    = $request->input('failureCode');
        $failureMessage = $request->input('failureMessage');

        if (! $transactionId) {
            return response()->json(['error' => 'transactionId manquant'], 400);
        }

        try {
            DB::transaction(function () use (
                $transactionId, $isSuccess, $event, $amount, $fees,
                $method, $account, $performedAt, $stateData, $failureCode, $failureMessage
            ) {
                $transaction = $this->findTransactionByKkiapayId($transactionId, $stateData);

                if (! $transaction) {
                    Log::channel('kkiapay')->warning('Webhook : transaction non trouvée', ['id' => $transactionId]);
                    return;
                }

                if ($transaction->status === 'completed') {
                    Log::channel('kkiapay')->info('Webhook : transaction déjà complète', ['id' => $transactionId]);
                    return;
                }

                $txType = $transaction->metadata['type'] ?? null;

                if ($transaction->type === 'credit') {
                    $this->processWebhookDeposit($transaction, $isSuccess, $event, $amount, $fees, $transactionId, $method, $account, $performedAt);
                } elseif ($txType === 'final_fee') {
                    $this->processWebhookFinalFee($transaction, $isSuccess, $event, $amount, $fees, $transactionId, $failureCode, $failureMessage);
                } else {
                    $this->processWebhookRegistration($transaction, $isSuccess, $event, $amount, $fees, $transactionId);
                }
            });

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('Webhook processing error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Processing error'], 500);
        }

        return response()->json(['success' => true]);
    }

    private function processWebhookDeposit(
        Transaction $transaction,
        bool $isSuccess,
        ?string $event,
        float $amount,
        float $fees,
        string $transactionId,
        ?string $method,
        ?string $account,
        ?string $performedAt
    ): void {
        if ($isSuccess && $event === 'transaction.success') {
            $transaction->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'fee'          => $fees,
                'total_amount' => $amount + $fees,
                'reference'    => $transactionId,
                'metadata'     => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event'          => 'transaction.success',
                    'kkiapay_method'         => $method,
                    'kkiapay_account'        => $account,
                    'kkiapay_fees'           => $fees,
                    'webhook_processed_at'   => now()->toIso8601String(),
                ]),
            ]);

            $wallet = $transaction->wallet;
            $wallet->balance += $transaction->amount;
            $wallet->save();
        } else {
            $transaction->update([
                'status'    => 'failed',
                'reference' => $transactionId,
                'metadata'  => array_merge($transaction->metadata ?? [], [
                    'kkiapay_event'        => $event ?? 'transaction.failed',
                    'webhook_processed_at' => now()->toIso8601String(),
                ]),
            ]);
        }
    }

    private function processWebhookFinalFee(
        Transaction $transaction,
        bool $isSuccess,
        ?string $event,
        float $amount,
        float $fees,
        string $transactionId,
        ?string $failureCode,
        ?string $failureMessage
    ): void {
        $fundingRequest = FundingRequest::lockForUpdate()->find($transaction->funding_request_id);
        if (! $fundingRequest) return;

        if ($isSuccess && $event === 'transaction.success') {
            $transaction->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'fee'          => $fees,
                'reference'    => $transactionId,
                'metadata'     => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event'          => 'transaction.success',
                    'webhook_processed_at'   => now()->toIso8601String(),
                ]),
            ]);

            /*
             * CORRECTION : on NE verse PAS le financement automatiquement.
             * On passe en 'pending_disbursement' pour que l'admin valide.
             */
            $fundingRequest->update([
                'final_fee_paid'    => true,
                'final_fee_paid_at' => now(),
                'status'            => 'pending_disbursement',
            ]);
        } else {
            $transaction->update([
                'status'    => 'failed',
                'reference' => $transactionId,
                'metadata'  => array_merge($transaction->metadata ?? [], [
                    'kkiapay_event'        => $event ?? 'transaction.failed',
                    'failure_code'         => $failureCode,
                    'failure_message'      => $failureMessage,
                    'webhook_processed_at' => now()->toIso8601String(),
                ]),
            ]);
        }
    }

    private function processWebhookRegistration(
        Transaction $transaction,
        bool $isSuccess,
        ?string $event,
        float $amount,
        float $fees,
        string $transactionId
    ): void {
        $fundingRequest = FundingRequest::lockForUpdate()->find($transaction->funding_request_id);
        if (! $fundingRequest) return;

        if ($isSuccess && $event === 'transaction.success') {
            $transaction->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'fee'          => $fees,
                'reference'    => $transactionId,
            ]);
            $fundingRequest->markAsPaid($transactionId, $amount);
        } else {
            $transaction->update(['status' => 'failed', 'reference' => $transactionId]);
            $fundingRequest->update(['payment_status' => 'failed']);
        }
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    private function getOrCreateWallet(): Wallet
    {
        $wallet = Wallet::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (! $wallet) {
            $wallet = Wallet::createForUser(auth()->id());
        }

        return $wallet;
    }

    private function findTransactionByKkiapayId(string $transactionId, ?string $stateData): ?Transaction
    {
        $tx = Transaction::where('reference', $transactionId)->lockForUpdate()->first();
        if ($tx) return $tx;

        $tx = Transaction::whereJsonContains('metadata', ['kkiapay_transaction_id' => $transactionId])->lockForUpdate()->first();
        if ($tx) return $tx;

        if ($stateData) {
            $parsed = json_decode($stateData, true);
            if (isset($parsed['internal_transaction_id'])) {
                return Transaction::where('transaction_id', $parsed['internal_transaction_id'])->lockForUpdate()->first();
            }
        }

        return null;
    }

    private function buildInitializeResponse(Transaction $transaction, float $amount, ?FundingRequest $fundingRequest = null): JsonResponse
    {
        $data = [
            'funding_request_id'      => $fundingRequest?->id,
            'internal_transaction_id' => $transaction->transaction_id,
            'user_id'                 => auth()->id(),
            'type'                    => $fundingRequest
                ? ((($transaction->metadata['type'] ?? '') === 'final_fee') ? 'final_fee' : 'registration_payment')
                : 'wallet_deposit',
        ];

        return response()->json([
            'success'     => true,
            'transaction' => $transaction,
            'kkiapay_config' => [
                'amount'  => (float) $amount,
                'key'     => $this->publicKey,
                'sandbox' => $this->sandbox,
                'data'    => json_encode($data),
            ],
        ]);
    }

    private function getRedirectUrl(FundingRequest $fundingRequest): string
    {
        if (method_exists($fundingRequest, 'pendingDocumentsCount') && $fundingRequest->pendingDocumentsCount() > 0) {
            return route('client.documents.required', $fundingRequest);
        }
        return route('client.requests.show', $fundingRequest);
    }

    // =========================================================================
    //  LEGACY processPayment
    // =========================================================================

    public function processPayment(Request $request, FundingRequest $fundingRequest): RedirectResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) abort(403);

        if ($fundingRequest->status !== 'draft' || $fundingRequest->payment_status !== 'pending') {
            return back()->with('error', 'Cette demande ne peut pas être payée.');
        }

        $response = $this->initialize($request, $fundingRequest);
        $responseData = $response->getData();

        if (! $responseData->success) {
            return back()->with('error', $responseData->message ?? 'Erreur');
        }

        $config = $responseData->kkiapay_config;

        return redirect()->away('https://widget.kkiapay.me/payment?' . http_build_query([
            'api_key'      => $config->key,
            'amount'       => $config->amount,
            'sandbox'      => $config->sandbox,
            'callback_url' => route('client.payment.verify'),
            'return_url'   => route('client.requests.show', $fundingRequest),
            'metadata'     => $config->data,
        ]));
    }
}
