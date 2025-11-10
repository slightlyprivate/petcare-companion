<?php

use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Pet\PetAppointmentController;
use App\Http\Controllers\Pet\PetController;
use Illuminate\Support\Facades\Route;

// TODO: Auth endpoints

// Pet endpoints
Route::prefix('pets')->group(function () {
  Route::get('/', [PetController::class, 'index'])->name('pets.index');
  Route::post('/', [PetController::class, 'store'])->name('pets.store');
  Route::get('/{pet}', [PetController::class, 'show'])->name('pets.show');
  Route::put('/{pet}', [PetController::class, 'update'])->name('pets.update');
  Route::delete('/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

  // Pet appointments endpoints
  Route::get('/{pet}/appointments', [PetAppointmentController::class, 'index'])->name('pets.appointments.index');
  Route::post('/{pet}/appointments', [PetAppointmentController::class, 'store'])->name('pets.appointments.store');
});


// Appointment endpoints
Route::prefix('appointments')->group(function () {
  Route::post('/', [AppointmentController::class, 'store'])->name('appointments.store');
  Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
  Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
  Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
});
