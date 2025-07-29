<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\GameAccountController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ContactController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/firebase', [AuthController::class, 'firebaseAuth']);

    // Games
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/featured', [GameController::class, 'featured']);
    Route::get('/games/categories', [GameController::class, 'categories']);
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::get('/games/{game}/services', [ServiceController::class, 'byGame']);
    Route::get('/games/{game}/accounts', [GameAccountController::class, 'byGame']);

    // Services
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/types', [ServiceController::class, 'types']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Game Accounts
    Route::get('/accounts', [GameAccountController::class, 'index']);
    Route::get('/accounts/featured', [GameAccountController::class, 'featured']);
    Route::get('/accounts/{account}', [GameAccountController::class, 'show']);

    // Contact
    Route::post('/contact', [ContactController::class, 'store']);
    Route::post('/newsletter/subscribe', [ContactController::class, 'subscribeNewsletter']);
});

// Protected routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Cart management
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{cart}', [CartController::class, 'update']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);

    // Order management
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/from-cart', [OrderController::class, 'createFromCart']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/customer-info', [OrderController::class, 'updateCustomerInfo']);

    // Payment processing
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/stripe/confirm', [PaymentController::class, 'confirmStripePayment']);
    Route::post('/payments/paypal/confirm', [PaymentController::class, 'confirmPayPalPayment']);

    // Support Tickets
    Route::apiResource('support-tickets', SupportTicketController::class);
    Route::post('/support-tickets/{ticket}/replies', [SupportTicketController::class, 'addReply']);
    Route::patch('/support-tickets/{ticket}/close', [SupportTicketController::class, 'close']);

    // User management
    Route::get('/user/stats', [UserController::class, 'stats']);
    Route::get('/user/orders', [UserController::class, 'orders']);
    Route::get('/user/accounts', [UserController::class, 'accounts']);
    Route::get('/user/accounts/{account}', [UserController::class, 'showAccount']);
    Route::post('/user/accounts/{account}/download', [UserController::class, 'downloadCredentials']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);
    Route::patch('/user/password', [UserController::class, 'changePassword']);
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);
    Route::patch('/user/notifications', [UserController::class, 'updateNotificationSettings']);
    Route::get('/user/notifications/unread-count', [UserController::class, 'unreadNotificationCount']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:super-admin|admin'])->prefix('v1/admin')->group(function () {
    // Users
    Route::apiResource('users', App\Http\Controllers\Api\Admin\UserController::class);
    Route::patch('/users/{user}/toggle-status', [App\Http\Controllers\Api\Admin\UserController::class, 'toggleStatus']);

    // Games
    Route::apiResource('games', App\Http\Controllers\Api\Admin\GameController::class);
    Route::patch('/games/{game}/toggle-status', [App\Http\Controllers\Api\Admin\GameController::class, 'toggleStatus']);

    // Services
    Route::apiResource('services', App\Http\Controllers\Api\Admin\ServiceController::class);
    Route::patch('/services/{service}/toggle-status', [App\Http\Controllers\Api\Admin\ServiceController::class, 'toggleStatus']);

    // Game Accounts
    Route::apiResource('accounts', App\Http\Controllers\Api\Admin\GameAccountController::class);
    Route::patch('/accounts/{account}/toggle-status', [App\Http\Controllers\Api\Admin\GameAccountController::class, 'toggleStatus']);

    // Orders
    Route::apiResource('orders', App\Http\Controllers\Api\Admin\OrderController::class);
    Route::patch('/orders/{order}/status', [App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);
    Route::get('/orders/export', [App\Http\Controllers\Api\Admin\OrderController::class, 'export']);

    // Support Tickets
    Route::apiResource('support-tickets', App\Http\Controllers\Api\Admin\SupportTicketController::class);
    Route::patch('/support-tickets/{ticket}/assign', [App\Http\Controllers\Api\Admin\SupportTicketController::class  [App\Http\Controllers\Api\Admin\SupportTicketController::class, 'assign']);

    // Dashboard
    Route::get('/dashboard/stats', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);
    Route::get('/dashboard/sales-data', [App\Http\Controllers\Api\Admin\DashboardController::class, 'salesData']);
    Route::get('/dashboard/users-data', [App\Http\Controllers\Api\Admin\DashboardController::class, 'usersData']);

    // Settings
    Route::get('/settings', [App\Http\Controllers\Api\Admin\SettingController::class, 'index']);
    Route::patch('/settings', [App\Http\Controllers\Api\Admin\SettingController::class, 'update']);
});

// Webhook routes (no authentication)
Route::post('/webhooks/stripe', [PaymentController::class, 'stripeWebhook']);
Route::post('/webhooks/paypal', [PaymentController::class, 'paypalWebhook']);
