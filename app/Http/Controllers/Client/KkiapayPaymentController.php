<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DocumentUser;
use App\Models\FundingRequest;
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
    private bool $sandbox;

    public function __construct()
    {
        $this->sandbox       = config('services.kkiapay.sandbox', true);
        $this->publicKey     = config('services.kkiapay.public_key');
        $this->privateKey    = config('services.kkiapay.private_key');
        $this->webhookSecret = config('services.kkiapay.webhook_secret', '');
    }

    /**
     * INITIALIZE — Crée la transaction pending
     * Gère deux cas : paiement inscription (avec FundingRequest) et dépôt wallet (sans FundingRequest)
     */
    public function initialize(?FundingRequest $fundingRequest = null): JsonResponse
    {
        Log::channel('kkiapay')->info('=== INITIALIZE PAYMENT ===', [
            'funding_request_id' => $fundingRequest?->id,
            'user_id' => auth()->id(),
            'type' => $fundingRequest ? 'registration_payment' : 'wallet_deposit',
        ]);

        // Cas 1 : Paiement d'inscription (avec FundingRequest)
        if ($fundingRequest) {
            return $this->initializeRegistrationPayment($fundingRequest);
        }

        // Cas 2 : Dépôt wallet (sans FundingRequest)
        return $this->initializeWalletDeposit();
    }

    /**
     * Initialiser un paiement d'inscription
     */
    private function initializeRegistrationPayment(FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        if (!$fundingRequest->isDraft()) {
            return response()->json(['success' => false, 'message' => 'Demande déjà traitée'], 400);
        }

        $typeFinancement = $fundingRequest->typeFinancement;
        if (!$typeFinancement) {
            return response()->json(['success' => false, 'message' => 'Type de financement introuvable'], 400);
        }

        $amount = $typeFinancement->registration_fee;

        $wallet = $this->getOrCreateWallet();

        // Vérifier transaction pending existante
        $existingTransaction = Transaction::where('funding_request_id', $fundingRequest->id)
            ->where('status', 'pending')
            ->first();

        if ($existingTransaction) {
            return $this->buildInitializeResponse($existingTransaction, $amount, $fundingRequest);
        }

        // Créer nouvelle transaction
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
            'description'        => "Frais d'inscription - {$fundingRequest->request_number}",
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
     * Initialiser un dépôt wallet
     */
    private function initializeWalletDeposit(): JsonResponse
    {
        $amount = request()->input('amount', 0);
        
        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Montant invalide'], 400);
        }

        $wallet = $this->getOrCreateWallet();

        // Créer transaction de dépôt
        $transaction = Transaction::create([
            'wallet_id'          => $wallet->id,
            'funding_request_id' => null, // Pas de funding request pour un dépôt
            'transaction_id'     => 'TXN-DEP-' . uniqid() . '-' . time(),
            'type'               => 'deposit',
            'amount'             => $amount,
            'fee'                => 0,
            'total_amount'       => $amount,
            'payment_method'     => 'kkiapay',
            'status'             => 'pending',
            'description'        => "Dépôt wallet",
            'metadata'           => [
                'type'                   => 'wallet_deposit',
                'user_id'                => auth()->id(),
                'kkiapay_initialized_at' => now()->toIso8601String(),
            ],
        ]);

        return $this->buildInitializeResponse($transaction, $amount, null);
    }

    /**
     * Récupérer ou créer le wallet
     */
    private function getOrCreateWallet(): Wallet
    {
        $wallet = Wallet::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$wallet) {
            $wallet = Wallet::createForUser(auth()->id());
        }

        return $wallet;
    }

    /**
     * VERIFY — Appelé par le FRONTEND après succès SDK
     * Gère les deux cas : paiement inscription et dépôt wallet
     */
    public function verify(Request $request): JsonResponse
    {
        Log::channel('kkiapay')->info('=== FRONTEND VERIFY ===', $request->only([
            'transactionId', 'funding_request_id', 'internal_transaction_id'
        ]));

        // Validation conditionnelle : funding_request_id est optionnel pour les dépôts
        $validated = $request->validate([
            'transactionId'            => 'required|string',
            'funding_request_id'       => 'nullable|integer|exists:funding_requests,id',
            'internal_transaction_id'  => 'required|string',
        ]);

        // Récupérer la transaction par son ID interne
        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        // Vérifier l'autorisation (user doit être propriétaire)
        if ($transaction->wallet->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        // Mettre à jour la référence Kkiapay si pas déjà fait
        if (empty($transaction->reference)) {
            $transaction->update(['reference' => $validated['transactionId']]);
        }

        // ==== CAS 1 : Dépôt Wallet (pas de funding_request_id) ====
        if (empty($validated['funding_request_id']) || $transaction->type === 'deposit') {
            return $this->verifyWalletDeposit($transaction, $validated['transactionId']);
        }

        // ==== CAS 2 : Paiement d'inscription (avec funding_request_id) ====
        return $this->verifyRegistrationPayment($transaction, $validated);
    }

    /**
     * Vérifier un dépôt wallet
     */
    private function verifyWalletDeposit(Transaction $transaction, string $kkiapayId): JsonResponse
    {
        // Si déjà complété
        if ($transaction->status === 'completed') {
            return response()->json([
                'success'      => true,
                'status'       => 'completed',
                'redirect_url' => route('client.wallet.show'),
                'message'      => 'Dépôt déjà traité',
            ]);
        }

        if ($transaction->status === 'failed') {
            return response()->json([
                'success' => false,
                'status'  => 'failed',
                'message' => 'Le dépôt a échoué.',
            ]);
        }

        // Vérifier via API Kkiapay
        return $this->verifyDepositViaApi($transaction, $kkiapayId);
    }

    /**
     * Vérifier un paiement d'inscription
     */
    private function verifyRegistrationPayment(Transaction $transaction, array $validated): JsonResponse
    {
        $fundingRequest = FundingRequest::findOrFail($validated['funding_request_id']);

        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        // Vérifier si déjà payé
        $freshRequest = $fundingRequest->fresh();
        if ($freshRequest->isPaid() && $freshRequest->isSubmitted()) {
            Log::channel('kkiapay')->info('✅ Already paid and submitted');
            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => $this->getRedirectUrl($freshRequest),
            ]);
        }

        // Si transaction complétée mais demande pas à jour
        if ($transaction->status === 'completed') {
            if (!$freshRequest->isPaid()) {
                Log::channel('kkiapay')->info('🔄 Syncing funding request from transaction');
                $freshRequest->markAsPaid($validated['transactionId'], $transaction->amount);
            }

            return response()->json([
                'success'      => true,
                'status'       => 'paid',
                'redirect_url' => $this->getRedirectUrl($freshRequest->fresh()),
            ]);
        }

        if ($transaction->status === 'failed') {
            return response()->json([
                'success' => false,
                'status'  => 'failed',
                'message' => 'Le paiement a échoué.',
            ]);
        }

        // Vérifier via API Kkiapay
        return $this->verifyViaApi($transaction, $freshRequest, $validated['transactionId']);
    }

    /**
     * Vérifier dépôt via API
     */
    private function verifyDepositViaApi(Transaction $transaction, string $kkiapayId): JsonResponse
    {
        try {
            Log::channel('kkiapay')->info('🔍 Verifying deposit via Kkiapay API', ['transactionId' => $kkiapayId]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Accept' => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/' . $kkiapayId);

            Log::channel('kkiapay')->info('Kkiapay API response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    // Traiter le dépôt réussi
                    $this->processSuccessfulDeposit($transaction, $kkiapayId, $data);

                    return response()->json([
                        'success'      => true,
                        'status'       => 'completed',
                        'redirect_url' => route('client.wallet.show'),
                    ]);
                }

                if (isset($data['status']) && in_array($data['status'], ['failed', 'cancelled'])) {
                    $transaction->markAsFailed($data['status']);

                    return response()->json([
                        'success' => false,
                        'status'  => 'failed',
                        'message' => 'Le dépôt a échoué.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('API verification error', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'status'  => 'pending',
            'message' => 'Vérification en cours...',
        ]);
    }

    /**
     * Traiter un dépôt réussi
     */
    private function processSuccessfulDeposit(Transaction $transaction, string $transactionId, array $data): void
    {
        Log::channel('kkiapay')->info('✅ Processing successful deposit', [
            'transaction_id' => $transaction->id,
        ]);

        DB::transaction(function () use ($transaction, $transactionId, $data) {
            // Mettre à jour la transaction
            $transaction->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'fee'          => $data['fees'] ?? 0,
                'reference'    => $transactionId,
                'metadata'     => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event'          => 'transaction.success',
                    'processed_at'           => now()->toIso8601String(),
                ]),
            ]);

            // Créditer le wallet
            $wallet = $transaction->wallet;
            $wallet->balance += $data['amount'] ?? $transaction->amount;
            $wallet->save();

            Log::channel('kkiapay')->info('✅ Wallet credited', [
                'wallet_id' => $wallet->id,
                'new_balance' => $wallet->balance,
            ]);
        });
    }

    /**
     * Vérifier le statut via API Kkiapay (pour paiement inscription)
     */
    private function verifyViaApi(Transaction $transaction, FundingRequest $fundingRequest, string $kkiapayId): JsonResponse
    {
        try {
            Log::channel('kkiapay')->info('🔍 Verifying via Kkiapay API', ['transactionId' => $kkiapayId]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Accept' => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/' . $kkiapayId);

            Log::channel('kkiapay')->info('Kkiapay API response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

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

                    return response()->json([
                        'success' => false,
                        'status'  => 'failed',
                        'message' => 'Le paiement a échoué.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('API verification error', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'status'  => 'pending',
            'message' => 'Vérification en cours...',
        ]);
    }

    /**
     * WEBHOOK — Appelé par Kkiapay
     * Gère les deux cas automatiquement
     */
    public function webhook(Request $request): JsonResponse
    {
        Log::channel('kkiapay')->info('╔════════════════════════════════════════════════════════╗');
        Log::channel('kkiapay')->info('║              WEBHOOK KKIAPAY REÇU                      ║');
        Log::channel('kkiapay')->info('╚════════════════════════════════════════════════════════╝');
        Log::channel('kkiapay')->info('Détails de la requête', [
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method'     => $request->method(),
            'url'        => $request->fullUrl(),
            'headers'    => $request->headers->all(),
            'payload'    => $request->all(),
        ]);

        // Vérification signature
        if ($this->webhookSecret) {
            $receivedSecret = $request->header('x-kkiapay-secret');
            
            Log::channel('kkiapay')->info('Vérification signature', [
                'received' => $receivedSecret,
                'expected' => $this->webhookSecret,
                'match'    => $receivedSecret === $this->webhookSecret,
            ]);

            if ($receivedSecret !== $this->webhookSecret) {
                Log::channel('kkiapay')->error('⛔ SIGNATURE INVALIDE');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        // Payload Kkiapay
        $transactionId  = $request->input('transactionId');
        $isSuccess      = $request->boolean('isPaymentSucces');
        $event          = $request->input('event');
        $amount         = (float) $request->input('amount', 0);
        $fees           = (float) $request->input('fees', 0);
        $account        = $request->input('account');
        $method         = $request->input('method');
        $failureCode    = $request->input('failureCode');
        $failureMessage = $request->input('failureMessage');
        $performedAt    = $request->input('performedAt');

        Log::channel('kkiapay')->info('Payload analysé', [
            'transactionId' => $transactionId,
            'isSuccess'     => $isSuccess,
            'event'         => $event,
            'amount'        => $amount,
            'fees'          => $fees,
        ]);

        if (!$transactionId) {
            Log::channel('kkiapay')->error('❌ transactionId manquant');
            return response()->json(['error' => 'Missing transactionId'], 400);
        }

        try {
            DB::transaction(function () use (
                $transactionId, $isSuccess, $event, $amount, $fees,
                $account, $method, $failureCode, $failureMessage, $performedAt
            ) {
                // Chercher la transaction par référence
                $transaction = Transaction::where('reference', $transactionId)
                    ->lockForUpdate()
                    ->first();

                // Si pas trouvé par référence, chercher dans metadata
                if (!$transaction) {
                    $transaction = Transaction::whereJsonContains('metadata', ['kkiapay_transaction_id' => $transactionId])
                        ->lockForUpdate()
                        ->first();
                }

                if (!$transaction) {
                    Log::channel('kkiapay')->warning('⚠️ Transaction non trouvée', ['id' => $transactionId]);
                    return;
                }

                Log::channel('kkiapay')->info('✅ Transaction trouvée', [
                    'db_id'              => $transaction->id,
                    'transaction_id'     => $transaction->transaction_id,
                    'current_status'     => $transaction->status,
                    'funding_request_id' => $transaction->funding_request_id,
                    'type'               => $transaction->type,
                ]);

                // Éviter double traitement
                if ($transaction->status === 'completed') {
                    Log::channel('kkiapay')->info('ℹ️ Déjà complétée, ignoré');
                    return;
                }

                // ==== CAS 1 : Dépôt Wallet ====
                if ($transaction->type === 'deposit' || empty($transaction->funding_request_id)) {
                    $this->processWebhookWalletDeposit($transaction, $isSuccess, $event, $amount, $fees, $transactionId, $method, $account, $performedAt);
                    return;
                }

                // ==== CAS 2 : Paiement Inscription ====
                $fundingRequest = FundingRequest::lockForUpdate()->find($transaction->funding_request_id);

                if (!$fundingRequest) {
                    Log::channel('kkiapay')->error('❌ FundingRequest non trouvée');
                    return;
                }

                Log::channel('kkiapay')->info('📋 FundingRequest trouvée', [
                    'id'     => $fundingRequest->id,
                    'status' => $fundingRequest->status,
                ]);

                if ($isSuccess && $event === 'transaction.success') {
                    Log::channel('kkiapay')->info('💰 PAIEMENT RÉUSSI - Traitement...');

                    $transaction->update([
                        'status'       => 'completed',
                        'completed_at' => now(),
                        'fee'          => $fees,
                        'reference'    => $transactionId,
                        'metadata'     => array_merge($transaction->metadata ?? [], [
                            'kkiapay_transaction_id' => $transactionId,
                            'kkiapay_event'          => 'transaction.success',
                            'kkiapay_method'         => $method,
                            'kkiapay_account'        => $account,
                            'kkiapay_fees'           => $fees,
                            'kkiapay_performed_at'   => $performedAt,
                            'webhook_processed_at'   => now()->toIso8601String(),
                        ]),
                    ]);

                    $fundingRequest->markAsPaid($transactionId, $amount);

                    Log::channel('kkiapay')->info('✅✅✅ TRAITEMENT RÉUSSI', [
                        'funding_request_id' => $fundingRequest->id,
                        'new_status'         => $fundingRequest->fresh()->status,
                        'payment_status'     => $fundingRequest->fresh()->payment_status,
                    ]);

                } else {
                    Log::channel('kkiapay')->warning('❌ PAIEMENT ÉCHOUÉ', [
                        'event'          => $event,
                        'failureCode'    => $failureCode,
                        'failureMessage' => $failureMessage,
                    ]);

                    $transaction->update([
                        'status'   => 'failed',
                        'reference' => $transactionId,
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'kkiapay_transaction_id' => $transactionId,
                            'kkiapay_event'          => $event ?? 'transaction.failed',
                            'failure_code'           => $failureCode,
                            'failure_message'        => $failureMessage,
                            'webhook_processed_at'   => now()->toIso8601String(),
                        ]),
                    ]);

                    $fundingRequest->update(['payment_status' => 'failed']);
                }
            });

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('💥 ERREUR CRITIQUE WEBHOOK', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Processing error: ' . $e->getMessage()], 500);
        }

        Log::channel('kkiapay')->info('✅ Webhook terminé avec succès (HTTP 200)');
        return response()->json(['status' => 'received', 'success' => true], 200);
    }

    /**
     * Traiter webhook dépôt wallet
     */
    private function processWebhookWalletDeposit(
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
        Log::channel('kkiapay')->info('💰 DÉPÔT WALLET - Traitement...');

        if ($isSuccess && $event === 'transaction.success') {
            $transaction->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'fee'          => $fees,
                'reference'    => $transactionId,
                'metadata'     => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event'          => 'transaction.success',
                    'kkiapay_method'         => $method,
                    'kkiapay_account'        => $account,
                    'kkiapay_fees'           => $fees,
                    'kkiapay_performed_at'   => $performedAt,
                    'webhook_processed_at'   => now()->toIso8601String(),
                ]),
            ]);

            // Créditer le wallet
            $wallet = $transaction->wallet;
            $wallet->balance += $amount;
            $wallet->save();

            Log::channel('kkiapay')->info('✅✅✅ DÉPÔT RÉUSSI', [
                'wallet_id'   => $wallet->id,
                'new_balance' => $wallet->balance,
            ]);
        } else {
            Log::channel('kkiapay')->warning('❌ DÉPÔT ÉCHOUÉ', [
                'event'          => $event,
            ]);

            $transaction->update([
                'status'   => 'failed',
                'reference' => $transactionId,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event'          => $event ?? 'transaction.failed',
                    'webhook_processed_at'   => now()->toIso8601String(),
                ]),
            ]);
        }
    }

    /**
     * Traiter un paiement réussi (pour paiement inscription)
     */
    private function processSuccessfulPayment(Transaction $transaction, FundingRequest $fundingRequest, string $transactionId, array $data): void
    {
        Log::channel('kkiapay')->info('✅ Processing successful payment', [
            'funding_request_id' => $fundingRequest->id,
        ]);

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

        Log::channel('kkiapay')->info('✅ FundingRequest updated', [
            'id'             => $fundingRequest->id,
            'new_status'     => $fundingRequest->fresh()->status,
            'payment_status' => $fundingRequest->fresh()->payment_status,
        ]);
    }

    /**
     * Construire la réponse d'initialisation
     */
    private function buildInitializeResponse(Transaction $transaction, float $amount, ?FundingRequest $fundingRequest = null): JsonResponse
    {
        $data = [
            'funding_request_id'      => $fundingRequest?->id,
            'internal_transaction_id' => $transaction->transaction_id,
            'user_id'                 => auth()->id(),
            'type'                    => $fundingRequest ? 'registration_payment' : 'wallet_deposit',
        ];

        return response()->json([
            'success'        => true,
            'transaction'    => $transaction,
            'kkiapay_config' => [
                'amount'  => (float) $amount,
                'key'     => $this->publicKey,
                'sandbox' => $this->sandbox,
                'data'    => json_encode($data),
            ],
        ]);
    }

    /**
     * URL de redirection après paiement
     */
    private function getRedirectUrl(FundingRequest $fundingRequest): string
    {
        if ($fundingRequest->pendingDocumentsCount() > 0) {
            return route('client.documents.required', $fundingRequest);
        }

        return route('client.requests.show', $fundingRequest);
    }

    /**
     * Initialise le paiement Kkiapay pour une demande existante (méthode legacy)
     */
    public function processPayment(Request $request, FundingRequest $fundingRequest): RedirectResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if ($fundingRequest->status !== 'draft' || $fundingRequest->payment_status !== 'pending') {
            return back()->with('error', 'Cette demande ne peut pas être payée.');
        }

        // Utiliser initialize() au lieu de la logique inline
        $response = $this->initialize($fundingRequest);
        
        if (!$response->getData()->success) {
            return back()->with('error', $response->getData()->message ?? 'Erreur');
        }

        $config = $response->getData()->kkiapay_config;
        
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