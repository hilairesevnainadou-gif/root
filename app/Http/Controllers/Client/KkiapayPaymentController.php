<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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
        $this->sandbox = config('services.kkiapay.sandbox', true);
        $this->publicKey = config('services.kkiapay.public_key');
        $this->privateKey = config('services.kkiapay.private_key');
        $this->webhookSecret = config('services.kkiapay.webhook_secret', '');
    }

    /**
     * INITIALIZE — Crée la transaction pending
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

        if (! $fundingRequest->isDraft()) {
            return response()->json(['success' => false, 'message' => 'Demande déjà traitée'], 400);
        }

        $typeFinancement = $fundingRequest->typeFinancement;
        if (! $typeFinancement) {
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
            'wallet_id' => $wallet->id,
            'funding_request_id' => $fundingRequest->id,
            'transaction_id' => 'TXN-'.uniqid().'-'.time(),
            'type' => 'payment',
            'amount' => $amount,
            'fee' => 0,
            'total_amount' => $amount,
            'payment_method' => 'kkiapay',
            'status' => 'pending',
            'description' => "Frais d'inscription - {$fundingRequest->request_number}",
            'metadata' => [
                'funding_request_id' => $fundingRequest->id,
                'type' => 'registration_fee',
                'user_id' => auth()->id(),
                'kkiapay_initialized_at' => now()->toIso8601String(),
            ],
        ]);

        return $this->buildInitializeResponse($transaction, $amount, $fundingRequest);
    }

    /**
     * Initialiser un dépôt wallet
     */
    public function initializeWalletDeposit(): JsonResponse
    {
        $amount = request()->input('amount', 0);  // Montant à créditer (sans frais)

        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Montant invalide'], 400);
        }

        $wallet = $this->getOrCreateWallet();

        // Créer transaction de dépôt
        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'funding_request_id' => null,
            'transaction_id' => 'WLT-DEP-'.strtoupper(uniqid()).'-'.time(),
            'type' => 'credit',
            'amount' => $amount,        // Montant qui sera crédité
            'fee' => 0,              // Les frais sont gérés par Kkiapay
            'total_amount' => $amount,        // Total payé (Kkiapay ajoute ses frais)
            'payment_method' => 'kkiapay',
            'status' => 'pending',
            'description' => 'Dépôt wallet - '.number_format($amount, 0, ',', ' ').' FCFA',
            'metadata' => [
                'type' => 'wallet_deposit',
                'amount_credited' => $amount,
                'user_id' => auth()->id(),
                'kkiapay_initialized_at' => now()->toIso8601String(),
            ],
        ]);

        // IMPORTANT: Envoyer seulement le montant à Kkiapay
        // Kkiapay ajoutera automatiquement ses frais de 1.9%
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

        if (! $wallet) {
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
        $validated = $request->validate([
            'transactionId' => 'required|string',
            'funding_request_id' => 'nullable|integer|exists:funding_requests,id',
            'internal_transaction_id' => 'required|string',
        ]);

        // 🔥 CORRECTION CRITIQUE : Mettre à jour la référence Kkiapay IMMÉDIATEMENT
        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])
            ->where('status', 'pending')
            ->first();

        if (! $transaction) {
            // Vérifier si déjà complétée
            $completedTransaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])
                ->where('status', 'completed')
                ->first();

            if ($completedTransaction) {
                return $this->handleCompletedTransaction($completedTransaction, $validated);
            }

            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        // Vérifier l'autorisation
        if ($transaction->wallet->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        // Sauvegarder la référence
        if (empty($transaction->reference) || $transaction->reference !== $validated['transactionId']) {
            $transaction->update(['reference' => $validated['transactionId']]);
        }

        // ==== CAS 1 : Dépôt Wallet ====
        // 🔥 CORRECTION : Vérifier le type de transaction AVANT tout
        if ($transaction->type === 'credit') {
            return $this->verifyWalletDeposit($transaction, $validated['transactionId']);
        }

        // ==== CAS 2 : Paiement d'inscription ====
        if (! empty($validated['funding_request_id'])) {
            return $this->verifyRegistrationPayment($transaction, $validated);
        }

        // Si on arrive ici, c'est une erreur
        return response()->json(['success' => false, 'message' => 'Type de transaction invalide'], 400);
    }

    public function verifyDeposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transactionId' => 'required|string',
            'internal_transaction_id' => 'required|string',
        ]);

        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->first();

        if (! $transaction || $transaction->wallet->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Transaction invalide'], 404);
        }

        // Appeler la même logique que KkiapayPaymentController
        $kkiapayController = app(KkiapayPaymentController::class);

        return $kkiapayController->verifyWalletDeposit($transaction, $validated['transactionId']);
    }

    /**
     * Gérer une transaction déjà complétée
     */
    private function handleCompletedTransaction(Transaction $transaction, array $validated): JsonResponse
    {
        // Si c'est un dépôt wallet
        if ($transaction->type === 'credit') {
            return response()->json([
                'success' => true,
                'status' => 'completed',
                'redirect_url' => route('client.wallet.show'),
                'message' => 'Dépôt déjà traité',
            ]);
        }

        // Si c'est un paiement d'inscription
        if ($transaction->funding_request_id) {
            $fundingRequest = FundingRequest::find($transaction->funding_request_id);
            if ($fundingRequest) {
                return response()->json([
                    'success' => true,
                    'status' => 'paid',
                    'redirect_url' => $this->getRedirectUrl($fundingRequest),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'status' => 'completed',
        ]);
    }

    /**
     * Vérifier un dépôt wallet
     */
    private function verifyWalletDeposit(Transaction $transaction, string $kkiapayId): JsonResponse
    {
        // Si déjà complété
        if ($transaction->status === 'completed') {
            return response()->json([
                'success' => true,
                'status' => 'completed',
                'redirect_url' => route('client.wallet.show'),
                'message' => 'Dépôt déjà traité',
            ]);
        }

        if ($transaction->status === 'failed') {
            return response()->json([
                'success' => false,
                'status' => 'failed',
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
        // 🔥 CORRECTION : Vérifier que funding_request_id existe
        if (empty($validated['funding_request_id'])) {
            return response()->json(['success' => false, 'message' => 'Funding request ID manquant'], 400);
        }

        $fundingRequest = FundingRequest::findOrFail($validated['funding_request_id']);

        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        // Vérifier si déjà payé
        $freshRequest = $fundingRequest->fresh();
        if ($freshRequest->isPaid() && $freshRequest->isSubmitted()) {
            Log::channel('kkiapay')->info('✅ Already paid and submitted');

            return response()->json([
                'success' => true,
                'status' => 'paid',
                'redirect_url' => $this->getRedirectUrl($freshRequest),
            ]);
        }

        // Si transaction complétée mais demande pas à jour
        if ($transaction->status === 'completed') {
            if (! $freshRequest->isPaid()) {
                Log::channel('kkiapay')->info('🔄 Syncing funding request from transaction');
                $freshRequest->markAsPaid($validated['transactionId'], $transaction->amount);
            }

            return response()->json([
                'success' => true,
                'status' => 'paid',
                'redirect_url' => $this->getRedirectUrl($freshRequest->fresh()),
            ]);
        }

        if ($transaction->status === 'failed') {
            return response()->json([
                'success' => false,
                'status' => 'failed',
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

            // 🔥 CORRECTION : URL sans espace !
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->privateKey,
                'Accept' => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/'.$kkiapayId);

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
                        'success' => true,
                        'status' => 'completed',
                        'redirect_url' => route('client.wallet.show'),
                        'new_balance' => $transaction->wallet->fresh()->balance,
                    ]);
                }

                if (isset($data['status']) && in_array($data['status'], ['failed', 'cancelled'])) {
                    $transaction->markAsFailed($data['status']);

                    return response()->json([
                        'success' => false,
                        'status' => 'failed',
                        'message' => 'Le dépôt a échoué.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('API verification error', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'status' => 'pending',
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
            // 🔥 RÉCUPÉRER LES FRAIS RÉELS DU WEBHOOK/API
            $actualFee = $data['fees'] ?? 0;
            $actualAmount = $data['amount'] ?? $transaction->amount;

            // Mettre à jour la transaction avec les vrais frais
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'fee' => $actualFee,           // Frais réels de Kkiapay
                'total_amount' => $actualAmount + $actualFee, // Total payé
                'reference' => $transactionId,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event' => 'transaction.success',
                    'kkiapay_fees' => $actualFee,
                    'kkiapay_amount_paid' => $actualAmount + $actualFee,
                    'processed_at' => now()->toIso8601String(),
                ]),            ]);

            // Créditer le wallet avec le montant demandé (pas le total avec frais)
            $wallet = $transaction->wallet;
            $wallet->balance += $transaction->amount; // Montant crédité (sans les frais)
            $wallet->save();

            Log::channel('kkiapay')->info('✅ Wallet credited', [
                'wallet_id' => $wallet->id,
                'amount_credited' => $transaction->amount,
                'fee_charged' => $actualFee,
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

            // 🔥 CORRECTION : URL sans espace !
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->privateKey,
                'Accept' => 'application/json',
            ])->get('https://api.kkiapay.me/api/v1/transactions/'.$kkiapayId);

            Log::channel('kkiapay')->info('Kkiapay API response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    $this->processSuccessfulPayment($transaction, $fundingRequest, $kkiapayId, $data);

                    return response()->json([
                        'success' => true,
                        'status' => 'paid',
                        'redirect_url' => $this->getRedirectUrl($fundingRequest->fresh()),
                    ]);
                }

                if (isset($data['status']) && in_array($data['status'], ['failed', 'cancelled'])) {
                    $transaction->markAsFailed($data['status']);
                    $fundingRequest->update(['payment_status' => 'failed']);

                    return response()->json([
                        'success' => false,
                        'status' => 'failed',
                        'message' => 'Le paiement a échoué.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('API verification error', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'status' => 'pending',
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
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        // Vérification signature
        if ($this->webhookSecret) {
            $receivedSecret = $request->header('x-kkiapay-secret');

            Log::channel('kkiapay')->info('Vérification signature', [
                'received' => $receivedSecret,
                'expected' => $this->webhookSecret,
                'match' => $receivedSecret === $this->webhookSecret,
            ]);

            if ($receivedSecret !== $this->webhookSecret) {
                Log::channel('kkiapay')->error('⛔ SIGNATURE INVALIDE');

                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        // Payload Kkiapay
        $transactionId = $request->input('transactionId');
        $isSuccess = $request->boolean('isPaymentSucces');
        $event = $request->input('event');
        $amount = (float) $request->input('amount', 0);
        $fees = (float) $request->input('fees', 0);
        $account = $request->input('account');
        $method = $request->input('method');
        $failureCode = $request->input('failureCode');
        $failureMessage = $request->input('failureMessage');
        $performedAt = $request->input('performedAt');
        $stateData = $request->input('stateData');

        Log::channel('kkiapay')->info('Payload analysé', [
            'transactionId' => $transactionId,
            'isSuccess' => $isSuccess,
            'event' => $event,
            'amount' => $amount,
            'fees' => $fees,
            'stateData' => $stateData,
        ]);

        if (! $transactionId) {
            Log::channel('kkiapay')->error('❌ transactionId manquant');

            return response()->json(['error' => 'Missing transactionId'], 400);
        }

        try {
            DB::transaction(function () use (
                $transactionId, $isSuccess, $event, $amount, $fees,
                $account, $method, $failureCode, $failureMessage, $performedAt, $stateData
            ) {
                // 🔥 CORRECTION : Chercher par référence OU par transaction_id interne dans stateData
                $transaction = null;

                // 1. Chercher par référence Kkiapay
                $transaction = Transaction::where('reference', $transactionId)
                    ->lockForUpdate()
                    ->first();

                // 2. Si pas trouvé, chercher dans metadata
                if (! $transaction) {
                    $transaction = Transaction::whereJsonContains('metadata', ['kkiapay_transaction_id' => $transactionId])
                        ->lockForUpdate()
                        ->first();
                }

                // 3. 🔥 NOUVEAU : Parser stateData pour trouver l'internal_transaction_id
                if (! $transaction && $stateData) {
                    $stateDataParsed = json_decode($stateData, true);
                    if (isset($stateDataParsed['internal_transaction_id'])) {
                        $transaction = Transaction::where('transaction_id', $stateDataParsed['internal_transaction_id'])
                            ->lockForUpdate()
                            ->first();

                        if ($transaction) {
                            Log::channel('kkiapay')->info('✅ Transaction trouvée via stateData', [
                                'internal_id' => $stateDataParsed['internal_transaction_id'],
                            ]);
                        }
                    }
                }

                if (! $transaction) {
                    Log::channel('kkiapay')->warning('⚠️ Transaction non trouvée', ['id' => $transactionId]);

                    return;
                }

                Log::channel('kkiapay')->info('✅ Transaction trouvée', [
                    'db_id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'current_status' => $transaction->status,
                    'funding_request_id' => $transaction->funding_request_id,
                    'type' => $transaction->type,
                ]);

                // Mettre à jour la référence si pas déjà faite
                if (empty($transaction->reference)) {
                    $transaction->update(['reference' => $transactionId]);
                }

                // Éviter double traitement
                if ($transaction->status === 'completed') {
                    Log::channel('kkiapay')->info('ℹ️ Déjà complétée, ignoré');

                    return;
                }

                // ==== CAS 1 : Dépôt Wallet ====
                if ($transaction->type === 'deposit' || empty($transaction->funding_request_id)) {
                    $this->processWebhookWalletDeposit(
                        $transaction, 
                        $isSuccess, 
                        $event, 
                        $amount, 
                        $fees, 
                        $transactionId, 
                        $method, 
                        $account, 
                        $performedAt
                    );

                    return;
                }

                // ==== CAS 2 : Paiement Inscription ====
                $fundingRequest = FundingRequest::lockForUpdate()->find($transaction->funding_request_id);

                if (! $fundingRequest) {
                    Log::channel('kkiapay')->error('❌ FundingRequest non trouvée');

                    return;
                }

                Log::channel('kkiapay')->info('📋 FundingRequest trouvée', [
                    'id' => $fundingRequest->id,
                    'status' => $fundingRequest->status,
                ]);

                if ($isSuccess && $event === 'transaction.success') {
                    Log::channel('kkiapay')->info('💰 PAIEMENT RÉUSSI - Traitement...');

                    $transaction->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'fee' => $fees,
                        'reference' => $transactionId,
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'kkiapay_transaction_id' => $transactionId,
                            'kkiapay_event' => 'transaction.success',
                            'kkiapay_method' => $method,
                            'kkiapay_account' => $account,
                            'kkiapay_fees' => $fees,
                            'kkiapay_performed_at' => $performedAt,
                            'webhook_processed_at' => now()->toIso8601String(),
                        ]),
                    ]);

                    $fundingRequest->markAsPaid($transactionId, $amount);

                    Log::channel('kkiapay')->info('TRAITEMENT RÉUSSI', [
                        'funding_request_id' => $fundingRequest->id,
                        'new_status' => $fundingRequest->fresh()->status,
                        'payment_status' => $fundingRequest->fresh()->payment_status,
                    ]);

                } else {
                    Log::channel('kkiapay')->warning('❌ PAIEMENT ÉCHOUÉ', [
                        'event' => $event,
                        'failureCode' => $failureCode,
                        'failureMessage' => $failureMessage,
                    ]);

                    $transaction->update([
                        'status' => 'failed',
                        'reference' => $transactionId,
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'kkiapay_transaction_id' => $transactionId,
                            'kkiapay_event' => $event ?? 'transaction.failed',
                            'failure_code' => $failureCode,
                            'failure_message' => $failureMessage,
                            'webhook_processed_at' => now()->toIso8601String(),
                        ]),
                    ]);

                    $fundingRequest->update(['payment_status' => 'failed']);
                }
            });

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('💥 ERREUR CRITIQUE WEBHOOK', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing error: '.$e->getMessage()], 500);
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
            // 🔥 IMPORTANT : Utiliser les frais réels du webhook
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'fee' => $fees,           // Frais réels de Kkiapay
                'total_amount' => $amount + $fees,  // Total réellement payé
                'reference' => $transactionId,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event' => 'transaction.success',
                    'kkiapay_method' => $method,
                    'kkiapay_account' => $account,
                    'kkiapay_fees' => $fees,
                    'kkiapay_amount_paid' => $amount + $fees,
                    'kkiapay_performed_at' => $performedAt,
                    'webhook_processed_at' => now()->toIso8601String(),
                ]),
            ]);

            // 🔥 IMPORTANT : Créditer le wallet avec le montant demandé (pas le total)
            // Le montant crédité = amount reçu - frais (ou le montant original de la transaction)
            $wallet = $transaction->wallet;
            $creditedAmount = $transaction->amount; // Montant original demandé
            $wallet->balance += $creditedAmount;
            $wallet->save();

            Log::channel('kkiapay')->info('✅✅✅ DÉPÔT RÉUSSI', [
                'wallet_id' => $wallet->id,
                'amount_credited' => $creditedAmount,
                'fee_charged' => $fees,
                'total_paid' => $amount + $fees,
                'new_balance' => $wallet->balance,
            ]);
        } else {
            Log::channel('kkiapay')->warning('❌ DÉPÔT ÉCHOUÉ', [
                'event' => $event,
            ]);

            $transaction->update([
                'status' => 'failed',
                'reference' => $transactionId,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'kkiapay_transaction_id' => $transactionId,
                    'kkiapay_event' => $event ?? 'transaction.failed',
                    'webhook_processed_at' => now()->toIso8601String(),
                ]),
            ]);
        }
    }

     /**
     * Vérifie le paiement et met à jour le statut
     */
    public function verifyPayment(Request $request, FundingRequest $fundingRequest): JsonResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'transactionId' => 'required|string',
            'amount_paid' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($fundingRequest, $validated) {
                // Mettre à jour la demande
                $fundingRequest->update([
                    'status' => 'submitted',
                    'payment_status' => 'paid',
                    'registration_fee_paid' => $validated['amount_paid'],
                    'payment_reference' => $validated['transactionId'],
                    'paid_at' => now(),
                    'submitted_at' => now(),
                ]);

                // Créer ou mettre à jour la transaction
                Transaction::create([
                    'user_id' => auth()->id(),
                    'funding_request_id' => $fundingRequest->id,
                    'type' => 'payment',
                    'amount' => $validated['amount_paid'],
                    'reference' => $validated['transactionId'],
                    'status' => 'completed',
                    'description' => "Frais d'inscription - {$fundingRequest->request_number}",
                    'completed_at' => now(),
                ]);

                Log::channel('payments')->info('Paiement confirmé', [
                    'funding_request_id' => $fundingRequest->id,
                    'transaction_id' => $validated['transactionId'],
                    'amount' => $validated['amount_paid'],
                ]);
            });

            return response()->json([
                'success' => true,
                'redirect_url' => route('client.requests.payment.success', $fundingRequest),
            ]);

        } catch (\Exception $e) {
            Log::channel('payments')->error('Erreur paiement', [
                'error' => $e->getMessage(),
                'funding_request_id' => $fundingRequest->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du paiement',
            ], 500);
        }
    }
    /**
     * Traiter un paiement réussi (pour paiement inscription)
     */
    private function processSuccessfulPayment(Transaction $transaction, FundingRequest $fundingRequest, string $transactionId, array $data): void
    {
        Log::channel('kkiapay')->info(' Processing successful payment', [
            'funding_request_id' => $fundingRequest->id,
        ]);

        $transaction->update([
            'status' => 'completed',
            'completed_at' => now(),
            'fee' => $data['fees'] ?? 0,
            'reference' => $transactionId,
            'metadata' => array_merge($transaction->metadata ?? [], [
                'kkiapay_transaction_id' => $transactionId,
                'kkiapay_event' => 'transaction.success',
                'kkiapay_method' => $data['method'] ?? null,
                'kkiapay_account' => $data['account'] ?? null,
                'processed_at' => now()->toIso8601String(),
            ]),
        ]);

        $fundingRequest->markAsPaid($transactionId, $data['amount'] ?? $transaction->amount);

        Log::channel('kkiapay')->info('✅ FundingRequest updated', [
            'id' => $fundingRequest->id,
            'new_status' => $fundingRequest->fresh()->status,
            'payment_status' => $fundingRequest->fresh()->payment_status,
        ]);
    }

    /**
     * Construire la réponse d'initialisation
     */
    private function buildInitializeResponse(Transaction $transaction, float $amount, ?FundingRequest $fundingRequest = null): JsonResponse
    {
        $data = [
            'funding_request_id' => $fundingRequest?->id,
            'internal_transaction_id' => $transaction->transaction_id,
            'user_id' => auth()->id(),
            'type' => $fundingRequest ? 'registration_payment' : 'wallet_deposit',
        ];

        return response()->json([
            'success' => true,
            'transaction' => $transaction,
            'kkiapay_config' => [
                'amount' => (float) $amount,
                'key' => $this->publicKey,
                'sandbox' => $this->sandbox,
                'data' => json_encode($data),
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

        if (! $response->getData()->success) {
            return back()->with('error', $response->getData()->message ?? 'Erreur');
        }

        $config = $response->getData()->kkiapay_config;

        return redirect()->away('https://widget.kkiapay.me/payment?'.http_build_query([
            'api_key' => $config->key,
            'amount' => $config->amount,
            'sandbox' => $config->sandbox,
            'callback_url' => route('client.payment.verify'),
            'return_url' => route('client.requests.show', $fundingRequest),
            'metadata' => $config->data,
        ]));
    }

    /**
     * WEBHOOK WALLET - POST (appelé par Kkiapay)
     */
    public function webhookWallet(Request $request): JsonResponse
    {
        Log::channel('kkiapay')->info('=== WEBHOOK WALLET POST ===', $request->all());

        // Même logique que webhook() mais spécifique wallet
        return $this->processWebhook($request, 'wallet');
    }

    /**
     * CALLBACK WALLET - GET (redirection navigateur après paiement)
     */
    public function walletCallback(Request $request): RedirectResponse
    {
        $transactionId = $request->input('transaction_id');
        
        Log::channel('kkiapay')->info('=== CALLBACK WALLET GET ===', ['transaction_id' => $transactionId]);

        // Rediriger vers le wallet avec message
        return redirect()->route('client.wallet.show')
            ->with('success', 'Paiement traité. Votre solde sera mis à jour dans quelques instants.');
    }

    /**
     * Méthode commune pour traiter les webhooks
     */
    private function processWebhook(Request $request, string $source): JsonResponse
    {
        // Vérification signature
        if ($this->webhookSecret) {
            $receivedSecret = $request->header('x-kkiapay-secret');
            if ($receivedSecret !== $this->webhookSecret) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $transactionId  = $request->input('transactionId');
        $isSuccess      = $request->boolean('isPaymentSucces');
        $event          = $request->input('event');
        $amount         = (float) $request->input('amount', 0);
        $fees           = (float) $request->input('fees', 0);
        $stateData      = $request->input('stateData');
        $method         = $request->input('method');
        $account        = $request->input('account');
        $performedAt    = $request->input('performedAt');

        Log::channel('kkiapay')->info("Processing webhook from {$source}", [
            'transactionId' => $transactionId,
            'isSuccess' => $isSuccess,
            'event' => $event,
        ]);

        try {
            DB::transaction(function () use ($transactionId, $isSuccess, $event, $amount, $fees, $stateData, $method, $account, $performedAt) {
                // Trouver la transaction
                $transaction = $this->findTransactionByKkiapayId($transactionId, $stateData);

                if (!$transaction) {
                    Log::channel('kkiapay')->error('Transaction non trouvée', ['id' => $transactionId]);
                    throw new \Exception('Transaction not found');
                }

                // Éviter double traitement
                if ($transaction->status === 'completed') {
                    Log::channel('kkiapay')->info('Déjà complétée');
                    return;
                }

                // 🔥 CORRECTION : Accepter 'credit' OU 'deposit'
                $isWalletDeposit = in_array($transaction->type, ['deposit', 'credit']) 
                    || empty($transaction->funding_request_id);

                if ($isWalletDeposit) {
                    $this->processWebhookWalletDeposit(
                        $transaction, 
                        $isSuccess, 
                        $event, 
                        $amount, 
                        $fees, 
                        $transactionId,
                        $method,
                        $account,
                        $performedAt
                    );
                } else {
                    // Traitement paiement inscription...
                    $this->processWebhookRegistration($transaction, $isSuccess, $event, $amount, $fees, $transactionId);
                }
            });

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('Erreur webhook', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Trouver une transaction par ID Kkiapay
     */
    private function findTransactionByKkiapayId(string $transactionId, ?string $stateData): ?Transaction
    {
        // 1. Chercher par référence
        $transaction = Transaction::where('reference', $transactionId)
            ->lockForUpdate()
            ->first();

        if ($transaction) {
            return $transaction;
        }

        // 2. Chercher dans metadata
        $transaction = Transaction::whereJsonContains('metadata', ['kkiapay_transaction_id' => $transactionId])
            ->lockForUpdate()
            ->first();

        if ($transaction) {
            return $transaction;
        }

        // 3. Chercher via stateData
        if ($stateData) {
            $stateDataParsed = json_decode($stateData, true);
            if (isset($stateDataParsed['internal_transaction_id'])) {
                return Transaction::where('transaction_id', $stateDataParsed['internal_transaction_id'])
                    ->lockForUpdate()
                    ->first();
            }
        }

        return null;
    }

    /**
     * Traiter webhook inscription
     */
    private function processWebhookRegistration(
        Transaction $transaction,
        bool $isSuccess,
        ?string $event,
        float $amount,
        float $fees,
        string $transactionId
    ): void {
        $fundingRequest = FundingRequest::lockForUpdate()->find($transaction->funding_request_id);

        if (!$fundingRequest) {
            Log::channel('kkiapay')->error('FundingRequest non trouvée');
            return;
        }

        if ($isSuccess && $event === 'transaction.success') {
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'fee' => $fees,
                'reference' => $transactionId,
            ]);

            $fundingRequest->markAsPaid($transactionId, $amount);
        } else {
            $transaction->update([
                'status' => 'failed',
                'reference' => $transactionId,
            ]);
            
            $fundingRequest->update(['payment_status' => 'failed']);
        }
    }
}