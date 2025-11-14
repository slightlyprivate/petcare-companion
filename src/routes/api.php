<?php

use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthRequestController;
use App\Http\Controllers\Auth\AuthVerificationController;
use App\Http\Controllers\Auth\User\NotificationPreferenceController;
use App\Http\Controllers\Auth\User\UserDataController;
use App\Http\Controllers\Gift\GiftController;
use App\Http\Controllers\Pet\PetAppointmentController;
use App\Http\Controllers\Pet\PetController;
use App\Http\Controllers\Pet\PetGiftController;
use App\Http\Controllers\Pet\PetRestoreController;
use App\Http\Controllers\Pet\Public\PetDirectoryController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Auth endpoints - with rate limiting for sensitive operations
Route::prefix('auth')->group(function () {
    Route::post('/request', [AuthRequestController::class, 'requestOtp'])
        ->middleware('throttle:auth.otp')
        ->name('auth.request-otp');
    Route::post('/verify', [AuthVerificationController::class, 'verifyOtp'])
        ->middleware('throttle:auth.verify')
        ->name('auth.verify-otp');
    Route::get('/me', [AuthController::class, 'show'])
        ->middleware('auth:sanctum')
        ->name('auth.me');
});

// Public endpoints (no rate limiting)
Route::prefix('public')->group(function () {
    Route::get('pets', [PetDirectoryController::class, 'index'])->name('public.pets.index');
});

// Webhook endpoints (no authentication required) - with rate limiting
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:webhook.stripe')
    ->name('webhooks.stripe');

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Read operations (no rate limiting)
    Route::prefix('appointments')->group(function () {
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    });

    Route::prefix('gifts')->group(function () {
        Route::get('/{gift}/receipt', [GiftController::class, 'exportReceipt'])->name('gifts.receipt.export');
    });

    Route::prefix('pets')->group(function () {
        Route::get('/', [PetController::class, 'index'])->name('pets.index');
        Route::get('/{pet}', [PetController::class, 'show'])->name('pets.show');
        Route::get('/{pet}/appointments', [PetAppointmentController::class, 'index'])->name('pets.appointments.index');
    });

    Route::prefix('user')->group(function () {
        Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index'])->name('user.notification-preferences.index');
    });

    // Write operations - Appointment endpoints (throttle:appointment.write)
    Route::middleware('throttle:appointment.write')->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
        Route::post('/pets/{pet}/appointments', [PetAppointmentController::class, 'store'])->name('pets.appointments.store');
    });

    // Write operations - Pet endpoints (throttle:pet.write)
    Route::middleware('throttle:pet.write')->group(function () {
        Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
        Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
        Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');
        Route::post('/pets/{pet}/restore', [PetRestoreController::class, 'restore'])->name('pets.restore');
    });

    // Write operations - Gift endpoints (throttle:gift.write)
    Route::middleware('throttle:gift.write')->group(function () {
        Route::post('/pets/{pet}/gifts', [PetGiftController::class, 'store'])->name('pets.gifts.store');
    });

    // Write operations - Notification endpoints (throttle:notification.write)
    Route::middleware('throttle:notification.write')->group(function () {
        Route::put('/user/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('user.notification-preferences.update');
        Route::post('/user/notification-preferences/disable-all', [NotificationPreferenceController::class, 'disableAll'])->name('user.notification-preferences.disable-all');
        Route::post('/user/notification-preferences/enable-all', [NotificationPreferenceController::class, 'enableAll'])->name('user.notification-preferences.enable-all');
    });

    // User data endpoints (strict rate limiting)
    Route::middleware('throttle:user.data.export')->group(function () {
        Route::get('/user/data/export', [UserDataController::class, 'exportData'])->name('user.data.export');
    });

    Route::middleware('throttle:user.data.delete')->group(function () {
        Route::delete('/user/data', [UserDataController::class, 'deleteData'])->name('user.data.delete');
    });
});
