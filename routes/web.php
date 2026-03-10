<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentVerificationController;
use App\Http\Controllers\Admin\FundingRequestController as AdminFundingRequestController;
use App\Http\Controllers\Admin\TypeDocController;
use App\Http\Controllers\Admin\TypeFinancementController as AdminTypeFinancementController;
use App\Http\Controllers\Admin\TypeFinancementDocController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DocumentController as ClientDocumentController;
use App\Http\Controllers\Client\FundingRequestController as ClientFundingRequestController;
use App\Http\Controllers\Client\KkiapayPaymentController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\TypeFinancementController as ClientTypeFinancementController;
use App\Http\Controllers\Client\WalletController as ClientWalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Publiques (Non authentifié)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentification
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/verification', [AuthController::class, 'showVerification'])->name('verification.show');
    Route::post('/verification', [AuthController::class, 'verifyCode'])->name('verification.verify');
    Route::post('/verification/resend', [AuthController::class, 'resendCode'])->name('verification.resend');
    Route::post('/verification/change', [AuthController::class, 'changeMethod'])->name('verification.change');
    Route::post('/verification/update-contact', [AuthController::class, 'updateContact'])->name('verification.update-contact');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Suivi public d'une demande (sans auth)
Route::get('/track/{requestNumber}', [ClientFundingRequestController::class, 'track'])->name('funding.track');

/*
|--------------------------------------------------------------------------
| Webhook Kkiapay (Public - Sans Auth)
|--------------------------------------------------------------------------
*/
// IMPORTANT: Cette route doit être accessible sans authentification
// mais elle garde le middleware 'web' pour la session et le CSRF
// Webhook principal (pour les paiements d'inscription)
Route::post('/payment/webhook', [KkiapayPaymentController::class, 'webhook'])
    ->name('client.payment.webhook');

// Webhook wallet (pour les dépôts) - POST pour Kkiapay
Route::post('/webhook/kkiapay/wallet', [KkiapayPaymentController::class, 'webhookWallet'])
    ->name('wallet.webhook');

// Callback GET pour redirection après paiement wallet
Route::get('/webhook/kkiapay/wallet', [KkiapayPaymentController::class, 'walletCallback'])
    ->name('wallet.callback');

/*
|--------------------------------------------------------------------------
| Routes Client (Authentifié)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard Client
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'show'])->name('client.profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('client.profile.update');
    Route::post('/profile/acknowledge', [ProfileController::class, 'acknowledgeModal'])->name('client.profile.acknowledge');

    // Gestion des entreprises (séparé)
    Route::prefix('profile/companies')->name('client.profile.companies.')->group(function () {
        Route::get('/', [ProfileController::class, 'companies'])->name('index');
        Route::get('/create', [ProfileController::class, 'createCompany'])->name('create');
        Route::post('/', [ProfileController::class, 'storeCompany'])->name('store');
        Route::get('/{company}', [ProfileController::class, 'showCompany'])->name('show');
        Route::get('/{company}/edit', [ProfileController::class, 'editCompany'])->name('edit');
        Route::patch('/{company}', [ProfileController::class, 'updateCompany'])->name('update');
        Route::delete('/{company}', [ProfileController::class, 'destroyCompany'])->name('destroy');
        Route::patch('/{company}/primary', [ProfileController::class, 'setPrimaryCompany'])->name('primary');
    });
    // Types de Financement
    Route::get('/financements', [ClientTypeFinancementController::class, 'index'])->name('client.financements.index');
    Route::get('/financements/{typeFinancement}', [ClientTypeFinancementController::class, 'show'])->name('client.financements.show');

    // PAIEMENT KKIAPAY - Routes spécifiques AVANT les routes avec {fundingRequest}
    Route::post('/requests/{fundingRequest}/payment/initialize', [KkiapayPaymentController::class, 'initialize'])
        ->name('client.payment.initialize');

    //  ROUTES DE VÉRIFICATION CORRIGÉES
    Route::post('/payment/verify', [KkiapayPaymentController::class, 'verify'])
        ->name('client.payment.verify');
    Route::post('/wallet/deposit/verify', [ClientWalletController::class, 'verifyDeposit'])
        ->name('client.wallet.deposit.verify');

    // Documents requis (après paiement)
    Route::get('/requests/{fundingRequest}/documents', [ClientDocumentController::class, 'required'])
        ->name('client.documents.required');

    // Demandes - Routes sans paramètre
    Route::get('/my-requests', [ClientFundingRequestController::class, 'index'])->name('client.requests.index');
    Route::get('/requests/create', [ClientFundingRequestController::class, 'create'])->name('client.requests.create');
    Route::post('/requests', [ClientFundingRequestController::class, 'store'])->name('client.requests.store');

    // Demandes - Routes avec {fundingRequest} en DERNIER pour éviter les conflits
    Route::get('/requests/{fundingRequest}/edit', [ClientFundingRequestController::class, 'edit'])->name('client.requests.edit');
    Route::patch('/requests/{fundingRequest}', [ClientFundingRequestController::class, 'update'])->name('client.requests.update');
    Route::delete('/requests/{fundingRequest}', [ClientFundingRequestController::class, 'destroy'])->name('client.requests.destroy');

    //  ROUTES DE PAIEMENT CORRIGÉES
    Route::get('/requests/{fundingRequest}/payment', [ClientFundingRequestController::class, 'payment'])
        ->name('client.requests.payment');
    Route::post('/requests/{fundingRequest}/payment/verify', [KkiapayPaymentController::class, 'verifyPayment'])
        ->name('client.requests.payment.verify');
    Route::get('/requests/{fundingRequest}/payment/success', [ClientFundingRequestController::class, 'paymentSuccess'])
        ->name('client.requests.payment.success');

    // Paiement direct via wallet
    Route::post('/requests/{fundingRequest}/payment/wallet', [KkiapayPaymentController::class, 'payWithWallet'])
        ->name('client.payment.wallet');

    Route::post('/requests/{fundingRequest}/payment/final/wallet', [KkiapayPaymentController::class, 'payFinalWithWallet'])
        ->name('client.payment.wallet.final');

    // Recheck — paiement déjà effectué non mis à jour
    Route::post('/requests/{fundingRequest}/payment/recheck', [KkiapayPaymentController::class, 'recheckPayment'])
        ->name('client.payment.recheck');

    Route::post('/requests/{fundingRequest}/payment/final/recheck', [KkiapayPaymentController::class, 'recheckFinalPayment'])
        ->name('client.payment.recheck.final');

    // Paiement des frais de dossier finals (statut approved → funded)

    Route::get('/requests/{fundingRequest}/payment/final', [ClientFundingRequestController::class, 'paymentFinal'])
        ->name('client.requests.payment.final');
    Route::post('/requests/{fundingRequest}/payment/final/verify', [KkiapayPaymentController::class, 'verifyFinalPayment'])
        ->name('client.requests.payment.final.verify');

    Route::get('/requests/{fundingRequest}', [ClientFundingRequestController::class, 'show'])->name('client.requests.show');

    // Route pour traiter le paiement (redirection vers Kkiapay)
    Route::post('/requests/{fundingRequest}/payment/process', [KkiapayPaymentController::class, 'processPayment'])
        ->name('client.payment.process');

    // Documents
    Route::get('/documents', [ClientDocumentController::class, 'index'])->name('client.documents.index');
    Route::post('/documents', [ClientDocumentController::class, 'store'])->name('client.documents.store');
    Route::get('/documents/{document}', [ClientDocumentController::class, 'show'])->name('client.documents.show');
    Route::get('/documents/{document}/download', [ClientDocumentController::class, 'download'])->name('client.documents.download');
    Route::delete('/documents/{document}', [ClientDocumentController::class, 'destroy'])->name('client.documents.destroy');

    // Wallet
    Route::prefix('wallet')->name('client.wallet.')->group(function () {
        // Affichage
        Route::get('/', [ClientWalletController::class, 'show'])->name('show');
        Route::get('/transactions', [ClientWalletController::class, 'transactions'])->name('transactions');

        // Dépôt (Kkiapay)
        Route::get('/deposit', [ClientWalletController::class, 'depositForm'])->name('deposit');
        Route::post('/deposit', [ClientWalletController::class, 'deposit'])->name('deposit.store');

        // Retrait
        Route::get('/withdraw', [ClientWalletController::class, 'withdrawForm'])->name('withdraw');
        Route::post('/withdraw', [ClientWalletController::class, 'withdraw'])->name('withdraw.store');
        Route::post('/withdraw/{transaction}/cancel', [ClientWalletController::class, 'cancelWithdrawal'])->name('withdraw.cancel');
    });

    // Notifications
    Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('client.notifications.index');
    Route::patch('/notifications/{notification}/read', [ClientNotificationController::class, 'markAsRead'])->name('client.notifications.read');
});
Route::patch('/notifications/read-all', [ClientNotificationController::class, 'markAllAsRead'])
    ->name('client.notifications.read-all');

/*
|--------------------------------------------------------------------------
| Routes Admin
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Routes Admin — bloc complet à remplacer dans web.php
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // ── Demandes ──────────────────────────────────────────────────────────
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [AdminFundingRequestController::class, 'index'])->name('index');
        Route::get('/export', [AdminFundingRequestController::class, 'export'])->name('export');
        Route::get('/{fundingRequest}', [AdminFundingRequestController::class, 'show'])->name('show');
        Route::patch('/{fundingRequest}/status', [AdminFundingRequestController::class, 'updateStatus'])->name('status');
        Route::post('/{fundingRequest}/assign', [AdminFundingRequestController::class, 'assign'])->name('assign');
        Route::post('/{fundingRequest}/committee', [AdminFundingRequestController::class, 'committeeDecision'])->name('committee');
    });

    // ── Documents ─────────────────────────────────────────────────────────
    // ATTENTION : '/documents/pending' DOIT être avant '/documents/{document}'
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/pending', [DocumentVerificationController::class, 'pending'])->name('pending');
        Route::post('/bulk', [DocumentVerificationController::class, 'bulkVerify'])->name('bulk');
        Route::get('/{document}', [DocumentVerificationController::class, 'serveFile'])->name('show');
        Route::get('/{document}/download', [DocumentVerificationController::class, 'download'])->name('download');
        Route::post('/{document}/verify', [DocumentVerificationController::class, 'verify'])->name('verify');
    });

    // ── Types de Financement ──────────────────────────────────────────────
    Route::prefix('typefinancements')->name('typefinancements.')->group(function () {
        // CRUD
        Route::get('/', [AdminTypeFinancementController::class, 'index'])->name('index');
        Route::get('/create', [AdminTypeFinancementController::class, 'create'])->name('create');
        Route::post('/', [AdminTypeFinancementController::class, 'store'])->name('store');
        Route::get('/{typeFinancement}/edit', [AdminTypeFinancementController::class, 'edit'])->name('edit');
        Route::patch('/{typeFinancement}', [AdminTypeFinancementController::class, 'update'])->name('update');
        Route::delete('/{typeFinancement}', [AdminTypeFinancementController::class, 'destroy'])->name('destroy');
        Route::patch('/{typeFinancement}/toggle', [AdminTypeFinancementController::class, 'toggleActive'])->name('toggle');

        // Docs requis — '/documents' AVANT '/{typeFinancement}/documents' pour éviter le conflit
        Route::get('/documents', [TypeFinancementDocController::class, 'index'])->name('documents');
        Route::get('/{typeFinancement}/documents', [TypeFinancementDocController::class, 'edit'])->name('documents.edit');
        Route::post('/{typeFinancement}/documents/sync', [TypeFinancementDocController::class, 'sync'])->name('documents.sync');
        Route::post('/{typeFinancement}/documents/attach', [TypeFinancementDocController::class, 'attach'])->name('documents.attach');
        Route::delete('/{typeFinancement}/documents/{typeDoc}', [TypeFinancementDocController::class, 'detach'])->name('documents.detach');
    });

    // ── Types de Documents ────────────────────────────────────────────────
    Route::prefix('typedocs')->name('typedocs.')->group(function () {
        Route::get('/', [TypeDocController::class, 'index'])->name('index');
        Route::post('/', [TypeDocController::class, 'store'])->name('store');
        Route::patch('/{typeDoc}', [TypeDocController::class, 'update'])->name('update');
        Route::delete('/{typeDoc}', [TypeDocController::class, 'destroy'])->name('destroy');
    });

    // ── Utilisateurs ──────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/verify', [UserController::class, 'verify'])->name('verify');
        Route::post('/{user}/toggle', [UserController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{user}/role', [UserController::class, 'promote'])->name('role');
    });

    // ── Wallets ───────────────────────────────────────────────────────────
    Route::prefix('wallets')->name('wallets.')->group(function () {
        // ATTENTION : '/withdrawals/pending' DOIT être avant '/{wallet}'
        Route::get('/withdrawals/pending', [AdminWalletController::class, 'withdrawals'])->name('withdrawals');
        Route::post('/withdrawals/{transaction}/approve', [AdminWalletController::class, 'approveWithdrawal'])->name('withdrawals.approve');
        Route::post('/withdrawals/{transaction}/reject', [AdminWalletController::class, 'rejectWithdrawal'])->name('withdrawals.reject');

        Route::get('/', [AdminWalletController::class, 'index'])->name('index');
        Route::get('/{wallet}', [AdminWalletController::class, 'show'])->name('show');
        Route::patch('/{wallet}/toggle', [AdminWalletController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{wallet}/adjust', [AdminWalletController::class, 'adjust'])->name('adjust');
    });

});
