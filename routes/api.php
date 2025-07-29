<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\GameAccountController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/firebase', [AuthController::class, 'firebaseAuth']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Games
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/featured', [GameController::class, 'featured']);
    Route::get('/games/popular', [GameController::class, 'popular']);
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::get('/games/{game}/services', [ServiceController::class, 'byGame']);
    Route::get('/games/{game}/accounts', [GameAccountController::class, 'byGame']);

    // Services
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/featured', [ServiceController::class, 'featured']);
    Route::get('/services/categories', [ServiceController::class, 'categories']);
    Route::get('/services/price-range', [ServiceController::class, 'priceRange']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Game Accounts
    Route::get('/accounts', [GameAccountController::class, 'index']);
    Route::get('/accounts/featured', [GameAccountController::class, 'featured']);
    Route::get('/accounts/platforms', [GameAccountController::class, 'platforms']);
    Route::get('/accounts/regions', [GameAccountController::class, 'regions']);
    Route::get('/accounts/price-range', [GameAccountController::class, 'priceRange']);
    Route::get('/accounts/{account}', [GameAccountController::class, 'show']);

    // Payment methods
    Route::get('/payment/methods', [PaymentController::class, 'getPaymentMethods']);

    // Contact & Newsletter
    Route::post('/contact', [App\Http\Controllers\ContactController::class, 'store']);
    Route::post('/newsletter/subscribe', [App\Http\Controllers\ContactController::class, 'subscribeNewsletter']);
});

// Protected API routes
Route::middleware(['auth:sanctum', 'verified'])->prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Cart management
    Route::get('/cart', [CartController::class, 'index']);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{item}', [CartController::class, 'update']);
    Route::delete('/cart/{item}', [CartController::class, 'destroy']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon']);

    // Order management
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/from-cart', [OrderController::class, 'createFromCart']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{order}/download', [OrderController::class, 'downloadCredentials']);

    // Checkout & Payment
    Route::post('/checkout/create-payment-intent', [CheckoutController::class, 'createPaymentIntent']);
    Route::post('/checkout/process-payment', [CheckoutController::class, 'processPayment']);
    Route::post('/checkout/confirm-payment', [CheckoutController::class, 'confirmPayment']);

    // Support Tickets
    Route::apiResource('support-tickets', SupportTicketController::class);
    Route::post('/support-tickets/{ticket}/replies', [SupportTicketController::class, 'addReply']);
    Route::patch('/support-tickets/{ticket}/close', [SupportTicketController::class, 'close']);
    Route::get('/support-tickets-stats', [SupportTicketController::class, 'statistics']);

    // User Profile & Settings
    Route::get('/user/profile', [App\Http\Controllers\Api\UserController::class, 'profile']);
    Route::patch('/user/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
    Route::patch('/user/password', [App\Http\Controllers\Api\UserController::class, 'changePassword']);
    Route::post('/user/avatar', [App\Http\Controllers\Api\UserController::class, 'uploadAvatar']);
    Route::get('/user/stats', [App\Http\Controllers\Api\UserController::class, 'stats']);
    Route::get('/user/notifications', [App\Http\Controllers\Api\UserController::class, 'notifications']);
    Route::patch('/user/notifications/read', [App\Http\Controllers\Api\UserController::class, 'markNotificationsRead']);
    Route::patch('/user/notifications/settings', [App\Http\Controllers\Api\UserController::class, 'updateNotificationSettings']);
});

// Admin API routes
Route::middleware(['auth:sanctum', 'role:super-admin|admin'])->prefix('v1/admin')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [App\Http\Controllers\Admin\DashboardController::class, 'stats']);
    Route::get('/dashboard/sales-data', [App\Http\Controllers\Admin\DashboardController::class, 'salesData']);
    Route::get('/dashboard/users-data', [App\Http\Controllers\Admin\DashboardController::class, 'usersData']);
    Route::get('/dashboard/orders-data', [App\Http\Controllers\Admin\DashboardController::class, 'ordersData']);

    // Users
    Route::apiResource('users', App\Http\Controllers\Admin\UserController::class);
    Route::patch('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus']);
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate']);

    // Games
    Route::apiResource('games', App\Http\Controllers\Admin\GameController::class);
    Route::patch('/games/{game}/toggle-status', [App\Http\Controllers\Admin\GameController::class, 'toggleStatus']);

    // Services
    Route::apiResource('services', App\Http\Controllers\Admin\ServiceController::class);
    Route::patch('/services/{service}/toggle-status', [App\Http\Controllers\Admin\ServiceController::class, 'toggleStatus']);
    Route::post('/services/bulk-action', [App\Http\Controllers\Admin\ServiceController::class, 'bulkAction']);

    // Game Accounts
    Route::apiResource('accounts', App\Http\Controllers\Admin\GameAccountController::class);
    Route::patch('/accounts/{account}/toggle-status', [App\Http\Controllers\Admin\GameAccountController::class, 'toggleStatus']);

    // Orders
    Route::apiResource('orders', App\Http\Controllers\Admin\OrderController::class);
    Route::patch('/orders/{order}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus']);
    Route::post('/orders/bulk-update-status', [App\Http\Controllers\Admin\OrderController::class, 'bulkUpdateStatus']);
    Route::get('/orders/export', [App\Http\Controllers\Admin\OrderController::class, 'export']);

    // Support Tickets
    Route::apiResource('admin-support-tickets', App\Http\Controllers\Admin\SupportTicketController::class);
    Route::patch('/admin-support-tickets/{ticket}/assign', [App\Http\Controllers\Admin\SupportTicketController::class, 'assign']);

    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index']);
    Route::patch('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update']);

    // Audit Logs
    Route::get('/audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index']);
});

// Webhook routes (no authentication)
Route::post('/webhooks/stripe', [PaymentController::class, 'stripeWebhook']);
Route::post('/webhooks/paypal', [PaymentController::class, 'paypalWebhook']);
