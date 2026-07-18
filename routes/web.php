<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/legal', [PageController::class, 'legal'])->name('legal');
Route::get('/history/diet-guide', [AnalysisController::class, 'dietGuide'])->name('analysis.diet-guide');



// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');
    Route::post('/analysis', [AnalysisController::class, 'store'])->name('analysis.store');
    Route::get('/analysis/result/{id}', [AnalysisController::class, 'showResult'])->name('analysis.result');
    Route::get('/history', [AnalysisController::class, 'history'])->name('analysis.history');
});
