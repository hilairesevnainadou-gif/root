<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentVerificationController;
use App\Http\Controllers\Admin\FundingRequestController as AdminFundingRequestController;
use App\Http\Controllers\Admin\TypeDocController;
use App\Http\Controllers\Admin\TypeFinancementController as AdminTypeFinancementController;
use App\Http\Controllers\Admin\UserController;
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
Route::get('/check-pwa', function () {
    $checks = [
        'manifest' => file_exists(public_path('manifest.json')),
        'sw' => file_exists(public_path('service-worker.js')),
        'icons' => [],
        'screenshots' => []
    ];

    $iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];
    foreach ($iconSizes as $size) {
        $path = public_path("icons/icon-{$size}x{$size}.png");
        $checks['icons'][$size] = file_exists($path);
    }

    // Screenshots optionnels
    $checks['screenshots']['screenshot1'] = file_exists(public_path('screenshots/screenshot1.png'));
    $checks['screenshots']['screenshot2'] = file_exists(public_path('screenshots/screenshot2.png'));

    return response()->json($checks);
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
Route::post('/payment/webhook', [KkiapayPaymentController::class, 'webhook'])
    ->name('client.payment.webhook');

/*
|--------------------------------------------------------------------------
| Routes Client (Authentifié)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard Client
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'show'])->name('client.profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('client.profile.update');
    Route::post('/profile/acknowledge', [ProfileController::class, 'acknowledgeModal'])->name('client.profile.acknowledge');

    // Types de Financement
    Route::get('/financements', [ClientTypeFinancementController::class, 'index'])->name('client.financements.index');
    Route::get('/financements/{typeFinancement}', [ClientTypeFinancementController::class, 'show'])->name('client.financements.show');

    // PAIEMENT KKIAPAY - Routes spécifiques AVANT les routes avec {fundingRequest}
    Route::post('/requests/{fundingRequest}/payment/initialize', [KkiapayPaymentController::class, 'initialize'])
        ->name('client.payment.initialize');
    Route::post('/payment/verify', [KkiapayPaymentController::class, 'verify'])
        ->name('client.payment.verify');

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
    Route::get('/requests/{fundingRequest}', [ClientFundingRequestController::class, 'show'])->name('client.requests.show');
    Route::get('/requests/{fundingRequest}/payment', [ClientFundingRequestController::class, 'payment'])
        ->name('client.requests.payment');

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
// Wallet - Routes complètes
Route::prefix('wallet')->name('client.wallet.')->middleware('auth')->group(function () {
    // Affichage
    Route::get('/', [ClientWalletController::class, 'show'])->name('show');
    Route::get('/transactions', [ClientWalletController::class, 'transactions'])->name('transactions');

    // Dépôt (Kkiapay)
    Route::get('/deposit', [ClientWalletController::class, 'depositForm'])->name('deposit');
    Route::post('/deposit', [ClientWalletController::class, 'deposit'])->name('deposit.store');
    Route::post('/deposit/verify', [ClientWalletController::class, 'verifyDeposit'])->name('deposit.verify');

    // Retrait
    Route::get('/withdraw', [ClientWalletController::class, 'withdrawForm'])->name('withdraw');
    Route::post('/withdraw', [ClientWalletController::class, 'withdraw'])->name('withdraw.store');
    Route::post('/withdraw/{transaction}/cancel', [ClientWalletController::class, 'cancelWithdrawal'])->name('withdraw.cancel');
});

// Webhook Kkiapay (public)
Route::post('/webhook/kkiapay/wallet', [ClientWalletController::class, 'webhook'])->name('wallet.webhook');
    // Notifications
    Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('client.notifications.index');
    Route::patch('/notifications/{notification}/read', [ClientNotificationController::class, 'markAsRead'])->name('client.notifications.read');
});

/*
|--------------------------------------------------------------------------
| Routes Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Demandes
    Route::get('/requests', [AdminFundingRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{fundingRequest}', [AdminFundingRequestController::class, 'show'])->name('requests.show');
    Route::patch('/requests/{fundingRequest}/status', [AdminFundingRequestController::class, 'updateStatus'])->name('requests.status');
    Route::post('/requests/{fundingRequest}/assign', [AdminFundingRequestController::class, 'assign'])->name('requests.assign');
    Route::post('/requests/{fundingRequest}/committee', [AdminFundingRequestController::class, 'committeeDecision'])->name('requests.committee');
    Route::get('/requests-export', [AdminFundingRequestController::class, 'export'])->name('requests.export');

    // Documents
    Route::get('/documents/pending', [DocumentVerificationController::class, 'pending'])->name('documents.pending');
    Route::get('/documents/{document}', [DocumentVerificationController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/verify', [DocumentVerificationController::class, 'verify'])->name('documents.verify');
    Route::post('/documents/bulk', [DocumentVerificationController::class, 'bulkVerify'])->name('documents.bulk');

    // Types Financement
    Route::get('/typefinancements', [AdminTypeFinancementController::class, 'index'])->name('typefinancements.index');
    Route::get('/typefinancements/create', [AdminTypeFinancementController::class, 'create'])->name('typefinancements.create');
    Route::post('/typefinancements', [AdminTypeFinancementController::class, 'store'])->name('typefinancements.store');
    Route::get('/typefinancements/{typeFinancement}/edit', [AdminTypeFinancementController::class, 'edit'])->name('typefinancements.edit');
    Route::patch('/typefinancements/{typeFinancement}', [AdminTypeFinancementController::class, 'update'])->name('typefinancements.update');
    Route::delete('/typefinancements/{typeFinancement}', [AdminTypeFinancementController::class, 'destroy'])->name('typefinancements.destroy');
    Route::patch('/typefinancements/{typeFinancement}/toggle', [AdminTypeFinancementController::class, 'toggleActive'])->name('typefinancements.toggle');

    // Types Documents
    Route::get('/typedocs', [TypeDocController::class, 'index'])->name('typedocs.index');
    Route::post('/typedocs', [TypeDocController::class, 'store'])->name('typedocs.store');
    Route::patch('/typedocs/{typeDoc}', [TypeDocController::class, 'update'])->name('typedocs.update');
    Route::delete('/typedocs/{typeDoc}', [TypeDocController::class, 'destroy'])->name('typedocs.destroy');

    // Utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    Route::post('/users/{user}/role', [UserController::class, 'promote'])->name('users.role');
});
