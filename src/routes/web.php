<?php

use App\Http\Controllers\PetWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pets.index.web');
});

Route::get('/pets', [PetWebController::class, 'index'])->name('pets.index.web');
Route::post('/pets', [PetWebController::class, 'store'])->name('pets.store.web');
