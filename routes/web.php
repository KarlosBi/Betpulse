<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Lobby del casino: público, como la portada de cualquier casino online
Route::view('/', 'casino')->name('home');

// Solo para visitantes sin sesión (si ya entraste, te manda al dashboard)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Máximo 5 intentos de login por minuto (frena ataques de fuerza bruta)
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Solo para usuarios con sesión (si no, redirige a /login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
