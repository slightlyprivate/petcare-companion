<?php

use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthRequestController;
use App\Http\Controllers\Auth\AuthVerificationController;
use App\Http\Controllers\Auth\User\NotificationPreferenceController;
use App\Http\Controllers\Auth\User\UserDataController;
use App\Http\Controllers\Auth\User\UserExportDownloadController;
use App\Http\Controllers\Credit\CreditPurchaseController;
use App\Http\Controllers\Gift\GiftController;
use App\Http\Controllers\GiftType\GiftTypeController;
use App\Http\Controllers\Pet\PetActivityController;
use App\Http\Controllers\Pet\PetAppointmentController;
use App\Http\Controllers\Pet\PetCaregiverInvitationController;
use App\Http\Controllers\Pet\PetController;
use App\Http\Controllers\Pet\PetGiftController;
use App\Http\Controllers\Pet\PetRestoreController;
use App\Http\Controllers\Pet\Public\PetDirectoryController;
use App\Http\Controllers\Pet\Public\PetReportController;
use App\Http\Controllers\PetRoutineController;
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
    // Lightweight status endpoint for auth checks (204 if authenticated; 401 otherwise)
    Route::get('/status', function () {
        return response()->noContent();
    })->middleware('auth:sanctum')->name('auth.status');
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('auth.logout');
});

// Public endpoints (no rate limiting)
Route::prefix('public')->group(function () {
    Route::get('pets', [PetDirectoryController::class, 'index'])->name('public.pets.index');
    Route::get('pets/{petId}', [PetDirectoryController::class, 'show'])->name('public.pets.show');
    Route::get('pet-reports/{petId}', [PetReportController::class, 'show'])->name('public.pet-reports.show');
    Route::get('gift-types', [GiftTypeController::class, 'index'])->name('public.gift-types.index');
    Route::get('gift-types/{giftType}', [GiftTypeController::class, 'show'])->name('public.gift-types.show');
});

// Webhook endpoints (no authentication required) - with rate limiting
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:webhook.stripe')
    ->name('webhooks.stripe');

// Download endpoint for user exports (controller validates signature & auth)
Route::get('/user/data/exports/{export}/download', [UserExportDownloadController::class, 'download'])
    ->middleware(['signed', 'cache.headers:no_store,private,max_age=0'])
    ->name('user.data.exports.download');

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Read operations (no rate limiting)
    Route::prefix('appointments')->group(function () {
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    });

    Route::prefix('gifts')->group(function () {
        Route::get('/{gift}/receipt', [GiftController::class, 'exportReceipt'])->name('gifts.receipt.export');
    });

    Route::prefix('credits')->group(function () {
        Route::get('/purchases', [CreditPurchaseController::class, 'index'])
            ->middleware('can:viewAny,' . \App\Models\CreditPurchase::class)
            ->name('credits.purchases.index');
        Route::get('/{creditPurchase}', [CreditPurchaseController::class, 'show'])
            ->middleware('can:view,creditPurchase')
            ->name('credits.show');
    });

    Route::prefix('pets')->group(function () {
        Route::get('/', [PetController::class, 'index'])->name('pets.index');
        Route::get('/{pet}', [PetController::class, 'show'])->name('pets.show');
        Route::get('/{pet}/appointments', [PetAppointmentController::class, 'index'])->name('pets.appointments.index');
        Route::get('/{pet}/activities', [PetActivityController::class, 'index'])->name('pets.activities.index');
        Route::get('/{pet}/routines', [PetRoutineController::class, 'index'])->name('pets.routines.index');
        // Today's routine tasks (auto-generates occurrences if missing)
        Route::get('/{pet}/routines/today', [\App\Http\Controllers\PetRoutineOccurrenceController::class, 'today'])->name('pets.routines.today');
    });

    Route::prefix('user')->group(function () {
        Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index'])->name('user.notification-preferences.index');
    });

    Route::prefix('caregiver-invitations')->group(function () {
        Route::get('/', [PetCaregiverInvitationController::class, 'index'])->name('caregiver-invitations.index');
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
        Route::post('/pets/{pet}/caregiver-invitations', [PetCaregiverInvitationController::class, 'store'])->name('pets.caregiver-invitations.store');
        Route::post('/caregiver-invitations/{token}/accept', [PetCaregiverInvitationController::class, 'accept'])->name('caregiver-invitations.accept');
        Route::delete('/caregiver-invitations/{invitation}', [PetCaregiverInvitationController::class, 'destroy'])->name('caregiver-invitations.destroy');
        Route::post('/pets/{pet}/activities', [PetActivityController::class, 'store'])->name('pets.activities.store');
        Route::delete('/activities/{activity}', [PetActivityController::class, 'destroy'])->name('activities.destroy');
        Route::post('/pets/{pet}/routines', [PetRoutineController::class, 'store'])->name('pets.routines.store');
        Route::patch('/routines/{routine}', [PetRoutineController::class, 'update'])->name('routines.update');
        Route::delete('/routines/{routine}', [PetRoutineController::class, 'destroy'])->name('routines.destroy');
        Route::post('/routine-occurrences/{occurrence}/complete', [\App\Http\Controllers\PetRoutineOccurrenceController::class, 'complete'])->name('routine-occurrences.complete');
    });

    // Write operations - Gift endpoints (throttle:gift.write)
    Route::middleware('throttle:gift.write')->group(function () {
        Route::post('/gifts', [GiftController::class, 'store'])->name('gifts.store');
        Route::post('/pets/{pet}/gifts', [PetGiftController::class, 'store'])->name('pets.gifts.store');
    });

    // Write operations - Credit endpoints (throttle:credit.write)
    Route::middleware('throttle:credit.write')->group(function () {
        Route::post('/credits/purchase', [CreditPurchaseController::class, 'store'])->name('credits.purchase');
    });

    // Admin endpoints - Gift Types (rate-limited and authorization via route middleware)
    Route::middleware('throttle:admin.write')->group(function () {
        Route::post('/gift-types', [GiftTypeController::class, 'store'])
            ->middleware('can:create,' . \App\Models\GiftType::class)
            ->name('gift-types.store');
        Route::put('/gift-types/{giftType}', [GiftTypeController::class, 'update'])
            ->middleware('can:update,giftType')
            ->name('gift-types.update');
        Route::delete('/gift-types/{giftType}', [GiftTypeController::class, 'destroy'])
            ->middleware('can:delete,giftType')
            ->name('gift-types.destroy');
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

    // Note: Moved the signed download route outside auth group to avoid redirect issues for unauthenticated requests.
});
