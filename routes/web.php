<?php

use App\Http\Controllers\OwnerController;
use App\Http\Controllers\EngineerController;
use App\Http\Controllers\CustomerSupportController;
use App\Http\Controllers\Auth\LoginController;


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/login', [LoginController::class, 'loginView'])->name('auth.login');
Route::POST('/login', [LoginController::class, 'login'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/owner-dashboard', [OwnerController::class, 'index'])->name('owner.dashboard');
    Route::get('/engineer-dashboard', [EngineerController::class, 'index'])->name('engineer.dashboard');
    Route::get('/cs-dashboard', [CustomerSupportController::class, 'index'])->name('cs.dashboard');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
