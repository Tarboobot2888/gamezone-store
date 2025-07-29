<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupportTicketController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/topup', [HomeController::class, 'topup'])->name('topup');
Route::get('/accounts', [HomeController::class, 'accounts'])->name('accounts');
Route::get('/account/{account}', [HomeController::class, 'accountDetail'])->name('account.detail');
Route::get('/cart', [HomeController::class, 'cart'])->name('cart');
Route::get('/checkout', [HomeController::class, 'checkout'])->name('checkout');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('contact.submit');

// Payment result pages
Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/failed', [PaymentController::class, 'paymentFailed'])->name('payment.failed');
Route::get('/payment/cancelled', [PaymentController::class, 'paymentCancelled'])->name('payment.cancelled');

// Order tracking (public)
Route::get('/orders/{orderNumber}/tracking', [HomeController::class, 'orderTracking'])->name('orders.tracking');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Password reset routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Email verification routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])->name('verification.send');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User dashboard routes
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [DashboardController::class, 'orderDetail'])->name('orders.show');
    Route::get('/accounts', [DashboardController::class, 'accounts'])->name('accounts');
    Route::get('/accounts/{account}', [DashboardController::class, 'accountDetail'])->name('accounts.show');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::patch('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Support Tickets
    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/create', [SupportTicketController::class, 'create'])->name('support-tickets.create');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('/support-tickets/{ticket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::post('/support-tickets/{ticket}/replies', [SupportTicketController::class, 'addReply'])->name('support-tickets.reply');
    Route::patch('/support-tickets/{ticket}/close', [SupportTicketController::class, 'close'])->name('support-tickets.close');
    
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::patch('/settings', [DashboardController::class, 'updateSettings'])->name('settings.update');
});

// Admin routes
Route::middleware(['auth', 'role:super-admin|admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Users management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [App\Http
    Route::patch('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('/stop-impersonating', [App\Http\Controllers\Admin\UserController::class, 'stopImpersonating'])->name('stop-impersonating');
    
    // Games management
    Route::resource('games', App\Http\Controllers\Admin\GameController::class);
    
    // Services management
    Route::resource('services', App\Http\Controllers\Admin\ServiceController::class);
    
    // Game Accounts management
    Route::resource('accounts', App\Http\Controllers\Admin\GameAccountController::class);
    
    // Orders management
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/export', [App\Http\Controllers\Admin\OrderController::class, 'export'])->name('orders.export');
    
    // Support Tickets management
    Route::get('/support-tickets', [App\Http\Controllers\Admin\SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/{ticket}', [App\Http\Controllers\Admin\SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::patch('/support-tickets/{ticket}/assign', [App\Http\Controllers\Admin\SupportTicketController::class, 'assign'])->name('support-tickets.assign');
    Route::post('/support-tickets/{ticket}/replies', [App\Http\Controllers\Admin\SupportTicketController::class, 'addReply'])->name('support-tickets.reply');
    
    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    
    // Audit logs
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    
    // System tools
    Route::get('/system', [AdminController::class, 'system'])->name('system');
    Route::post('/system/backup', [AdminController::class, 'createBackup'])->name('system.backup');
    Route::post('/system/restore', [AdminController::class, 'restoreBackup'])->name('system.restore');
    Route::post('/system/clear-cache', [AdminController::class, 'clearCache'])->name('system.clear-cache');
});
