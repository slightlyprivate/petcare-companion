<?php

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\PetWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pets.index.web');
});

// Authentication routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])
    ->name('login.post')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pets', [PetWebController::class, 'index'])->name('pets.index.web');
    Route::post('/pets', [PetWebController::class, 'store'])->name('pets.store.web');
});
