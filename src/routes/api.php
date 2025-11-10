<?php

use App\Http\Controllers\PetListController;
use App\Http\Controllers\PetCreateController;
use App\Http\Controllers\PetShowController;
use App\Http\Controllers\PetUpdateController;
use App\Http\Controllers\PetDeleteController;
use App\Http\Controllers\PetAppointmentsController;
use App\Http\Controllers\AppointmentCreateController;
use App\Http\Controllers\AppointmentDirectCreateController;
use App\Http\Controllers\AppointmentShowController;
use App\Http\Controllers\AppointmentUpdateController;
use App\Http\Controllers\AppointmentDeleteController;
use Illuminate\Support\Facades\Route;

// Pet endpoints
Route::get('/pets', PetListController::class)->name('pets.index');
Route::post('/pets', PetCreateController::class)->name('pets.store');
Route::get('/pets/{pet}', PetShowController::class)->name('pets.show');
Route::put('/pets/{pet}', PetUpdateController::class)->name('pets.update');
Route::delete('/pets/{pet}', PetDeleteController::class)->name('pets.destroy');

// Pet appointments endpoints
Route::get('/pets/{pet}/appointments', PetAppointmentsController::class)->name('pets.appointments.index');
Route::post('/pets/{pet}/appointments', AppointmentCreateController::class)->name('pets.appointments.store');

// Appointment endpoints
Route::post('/appointments', AppointmentDirectCreateController::class)->name('appointments.store');
Route::get('/appointments/{appointment}', AppointmentShowController::class)->name('appointments.show');
Route::put('/appointments/{appointment}', AppointmentUpdateController::class)->name('appointments.update');
Route::delete('/appointments/{appointment}', AppointmentDeleteController::class)->name('appointments.destroy');
