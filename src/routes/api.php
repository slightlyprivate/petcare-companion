<?php

use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthRequestController;
use App\Http\Controllers\Auth\AuthVerificationController;
use App\Http\Controllers\Auth\User\NotificationPreferenceController;
use App\Http\Controllers\Auth\User\UserDataController;
use App\Http\Controllers\Donation\DonationController;
use App\Http\Controllers\Pet\PetAppointmentController;
use App\Http\Controllers\Pet\PetController;
use App\Http\Controllers\Pet\PetDonationController;
use App\Http\Controllers\Pet\Public\PetDirectoryController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Auth endpoints
Route::prefix('auth')->group(function () {
    Route::post('/request', [AuthRequestController::class, 'requestOtp'])
        ->name('auth.request-otp');
    Route::post('/verify', [AuthVerificationController::class, 'verifyOtp'])
        ->name('auth.verify-otp');
    Route::get('/me', [AuthController::class, 'show'])
        ->middleware('auth:sanctum')
        ->name('auth.me');
});

// Public endpoints
Route::prefix('public')->group(function () {
    Route::get('pets', [PetDirectoryController::class, 'index'])->name('public.pets.index');
});

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Appointment endpoints
    Route::prefix('appointments')
        ->group(function () {
            Route::post('/', [AppointmentController::class, 'store'])->name('appointments.store');
            Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
            Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
            Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
        });
    // Donation endpoints
    Route::prefix('donations')
        ->group(function () {
            Route::get('/{id}/receipt', [DonationController::class, 'exportReceipt'])->name('donations.receipt.export');
        });
    // Pet endpoints
    Route::prefix('pets')
        ->group(function () {
            Route::get('/', [PetController::class, 'index'])->name('pets.index');
            Route::post('/', [PetController::class, 'store'])->name('pets.store');
            Route::get('/{pet}', [PetController::class, 'show'])->name('pets.show');
            Route::put('/{pet}', [PetController::class, 'update'])->name('pets.update');
            Route::delete('/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

            // Pet appointments endpoints
            Route::get('/{pet}/appointments', [PetAppointmentController::class, 'index'])->name('pets.appointments.index');
            Route::post('/{pet}/appointments', [PetAppointmentController::class, 'store'])->name('pets.appointments.store');

            // Pet donations endpoint
            Route::post('/{pet}/donate', [PetDonationController::class, 'store'])->name('pets.donations.store');
        });
    // User endpoints
    Route::prefix('user')
        ->group(function () {
            // User data
            Route::get('/data/export', [UserDataController::class, 'exportData'])->name('user.data.export');
            Route::delete('/data/delete', [UserDataController::class, 'deleteData'])->name('user.data.delete');

            // Notification preferences
            Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index'])->name('user.notification-preferences.index');
            Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('user.notification-preferences.update');
            Route::post('/notification-preferences/disable-all', [NotificationPreferenceController::class, 'disableAll'])->name('user.notification-preferences.disable-all');
            Route::post('/notification-preferences/enable-all', [NotificationPreferenceController::class, 'enableAll'])->name('user.notification-preferences.enable-all');
        });
});

// Webhook endpoints (no authentication required)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
