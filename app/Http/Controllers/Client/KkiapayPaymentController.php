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
     */
    public function initialize(FundingRequest $fundingRequest): JsonResponse
    {
        Log::channel('kkiapay')->info('=== INITIALIZE PAYMENT ===', [
            'funding_request_id' => $fundingRequest->id,
            'user_id' => auth()->id(),
        ]);

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

        // Créer ou récupérer le wallet
        $wallet = Wallet::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$wallet) {
            $wallet = Wallet::createForUser(auth()->id());
        }

        // Vérifier transaction pending existante
        $existingTransaction = Transaction::where('funding_request_id', $fundingRequest->id)
            ->where('status', 'pending')
            ->first();

        if ($existingTransaction) {
            return $this->buildInitializeResponse($existingTransaction, $fundingRequest, $amount);
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

        return $this->buildInitializeResponse($transaction, $fundingRequest, $amount);
    }

    /**
     * VERIFY — Appelé par le FRONTEND après succès SDK
     */
    public function verify(Request $request): JsonResponse
    {
        Log::channel('kkiapay')->info('=== FRONTEND VERIFY ===', $request->only([
            'transactionId', 'funding_request_id', 'internal_transaction_id'
        ]));

        $validated = $request->validate([
            'transactionId'            => 'required|string',
            'funding_request_id'       => 'required|integer|exists:funding_requests,id',
            'internal_transaction_id'  => 'required|string',
        ]);

        $fundingRequest = FundingRequest::findOrFail($validated['funding_request_id']);

        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        // Mettre à jour la référence Kkiapay sur la transaction
        $transaction = Transaction::where('transaction_id', $validated['internal_transaction_id'])
            ->where('funding_request_id', $fundingRequest->id)
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction introuvable'], 404);
        }

        // Mettre à jour la référence si pas déjà fait
        if (empty($transaction->reference)) {
            $transaction->update(['reference' => $validated['transactionId']]);
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

        // ==== MODE SANDBOX : Vérifier directement via API Kkiapay ====
        if ($this->sandbox) {
            return $this->verifyViaApi($transaction, $freshRequest, $validated['transactionId']);
        }

        // En attente du webhook (production)
        return response()->json([
            'success' => true,
            'status'  => 'pending',
            'message' => 'Confirmation en cours...',
        ]);
    }

    /**
     * Vérifier le statut via API Kkiapay (sandbox uniquement)
     */
    private function verifyViaApi(Transaction $transaction, FundingRequest $fundingRequest, string $kkiapayId): JsonResponse
    {
        try {
            Log::channel('kkiapay')->info('🔍 Verifying via Kkiapay API', ['transactionId' => $kkiapayId]);

            // Appel API Kkiapay pour vérifier le statut
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

                // Si transaction réussie selon Kkiapay
                if (isset($data['status']) && $data['status'] === 'success') {
                    // Traiter comme un webhook
                    $this->processSuccessfulPayment($transaction, $fundingRequest, $kkiapayId, $data);

                    return response()->json([
                        'success'      => true,
                        'status'       => 'paid',
                        'redirect_url' => $this->getRedirectUrl($fundingRequest->fresh()),
                    ]);
                }

                // Si échouée
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

        // Si API indisponible ou status inconnu, rester en pending
        return response()->json([
            'success' => true,
            'status'  => 'pending',
            'message' => 'Vérification en cours...',
        ]);
    }

    /**
     * WEBHOOK — Appelé par Kkiapay
     */
    public function webhook(Request $request): JsonResponse
    {
        Log::channel('kkiapay')->info('=== WEBHOOK RECEIVED ===', [
            'ip'       => $request->ip(),
            'method'   => $request->method(),
            'all_data' => $request->all(),
        ]);

        // Vérification signature
        if ($this->webhookSecret) {
            $receivedSecret = $request->header('x-kkiapay-secret');
            
            if ($receivedSecret !== $this->webhookSecret) {
                Log::channel('kkiapay')->error('Invalid signature', [
                    'received' => $receivedSecret,
                    'expected' => $this->webhookSecret,
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        // Payload
        $transactionId  = $request->input('transactionId');
        $isSuccess      = $request->boolean('isPaymentSucces');
        $event          = $request->input('event');
        $amount         = (float) $request->input('amount', 0);
        $fees           = (float) $request->input('fees', 0);

        Log::channel('kkiapay')->info('Webhook parsed', [
            'transactionId' => $transactionId,
            'isSuccess'     => $isSuccess,
            'event'         => $event,
        ]);

        if (!$transactionId) {
            return response()->json(['error' => 'Missing transactionId'], 400);
        }

        try {
            DB::transaction(function () use ($transactionId, $isSuccess, $event, $amount, $fees, $request) {
                // Chercher la transaction par référence
                $transaction = Transaction::where('reference', $transactionId)
                    ->orWhereJsonContains('metadata->kkiapay_transaction_id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    // Chercher aussi dans les metadata
                    $transaction = Transaction::whereJsonContains('metadata', ['kkiapay_transaction_id' => $transactionId])
                        ->lockForUpdate()
                        ->first();
                }

                if (!$transaction) {
                    Log::channel('kkiapay')->warning('Transaction not found', ['id' => $transactionId]);
                    return;
                }

                Log::channel('kkiapay')->info('Found transaction', [
                    'id'     => $transaction->id,
                    'status' => $transaction->status,
                ]);

                // Éviter double traitement
                if ($transaction->status === 'completed') {
                    Log::channel('kkiapay')->info('Already completed');
                    return;
                }

                $fundingRequest = FundingRequest::lockForUpdate()->find($transaction->funding_request_id);

                if (!$fundingRequest) {
                    Log::channel('kkiapay')->error('FundingRequest not found');
                    return;
                }

                if ($isSuccess && $event === 'transaction.success') {
                    $this->processSuccessfulPayment($transaction, $fundingRequest, $transactionId, [
                        'amount' => $amount,
                        'fees'   => $fees,
                        'method' => $request->input('method'),
                        'account'=> $request->input('account'),
                    ]);
                } else {
                    // Échec
                    $transaction->update([
                        'status'   => 'failed',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'kkiapay_transaction_id' => $transactionId,
                            'kkiapay_event'          => $event,
                            'failure_code'           => $request->input('failureCode'),
                            'failure_message'        => $request->input('failureMessage'),
                        ]),
                    ]);

                    $fundingRequest->update(['payment_status' => 'failed']);
                    Log::channel('kkiapay')->warning('Payment failed');
                }
            });

        } catch (\Exception $e) {
            Log::channel('kkiapay')->error('Webhook error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Processing error'], 500);
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Traiter un paiement réussi (utilisé par webhook et API)
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
            'metadata'     => array_merge($transaction->metadata ?? [], [
                'kkiapay_transaction_id' => $transactionId,
                'kkiapay_event'          => 'transaction.success',
                'kkiapay_method'         => $data['method'] ?? null,
                'kkiapay_account'        => $data['account'] ?? null,
                'processed_at'           => now()->toIso8601String(),
            ]),
        ]);

        // Mettre à jour la demande (CRITIQUE)
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
    private function buildInitializeResponse(Transaction $transaction, FundingRequest $fundingRequest, float $amount): JsonResponse
    {
        return response()->json([
            'success'        => true,
            'transaction'    => $transaction,
            'kkiapay_config' => [
                'amount'  => (float) $amount,
                'key'     => $this->publicKey,
                'sandbox' => $this->sandbox,
                'data'    => json_encode([
                    'funding_request_id'      => $fundingRequest->id,
                    'internal_transaction_id' => $transaction->transaction_id,
                    'user_id'                 => auth()->id(),
                ]),
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
}