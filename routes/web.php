<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ChooseBrandController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoAccessController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SwitchBrandController;
use App\Http\Controllers\TrialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Shown to users without a role or brand; must stay outside the "access" middleware.
    Route::get('/no-access', NoAccessController::class)->name('no-access');

    Route::middleware('access')->group(function () {
        Route::redirect('/', '/dashboard')->name('home');

        Route::get('/brands/choose', ChooseBrandController::class)->name('brands.choose');
        Route::post('/brands/{brand}/switch', SwitchBrandController::class)->name('brands.switch');
        Route::resource('brands', BrandController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');
        Route::resource('roles', RoleController::class)->except('show');
        Route::resource('permissions', PermissionController::class)->except('show');
        Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);

        // Everything below works inside the current brand.
        Route::middleware('brand')->group(function () {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');

            Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
            Route::post('customers/{customer}/restore', [CustomerController::class, 'restore'])->withTrashed()->name('customers.restore');
            Route::resource('customers', CustomerController::class);

            Route::post('subscriptions/{subscription}/status', [SubscriptionController::class, 'status'])->name('subscriptions.status');
            Route::resource('subscriptions', SubscriptionController::class)->except('destroy');

            Route::post('orders/{order}/payments', [OrderController::class, 'pay'])->name('orders.pay');
            Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
            Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);

            Route::resource('payments', PaymentController::class)->only(['index', 'show']);

            Route::post('trials/{trial}/status', [TrialController::class, 'status'])->name('trials.status');
            Route::resource('trials', TrialController::class);

            Route::post('contacts/{contact}/status', [ContactController::class, 'status'])->name('contacts.status');
            Route::resource('contacts', ContactController::class)->only(['index', 'show', 'destroy']);

            Route::resource('plans', PlanController::class)->except('show');
            Route::resource('packs', PackController::class)->except('show');
        });
    });
});
