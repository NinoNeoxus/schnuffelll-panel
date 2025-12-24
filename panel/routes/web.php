<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('/', [AuthController::class, 'login'])->name('login'); // Named 'login' for Auth middleware
Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Node Management
    Route::prefix('admin/nodes')->name('admin.nodes.')->group(function () {
        Route::get('/', [NodeController::class, 'index'])->name('index');
        Route::get('/create', [NodeController::class, 'create'])->name('create');
        Route::post('/', [NodeController::class, 'store'])->name('store');
    });

    // Settings
    Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\SettingsController::class, 'update'])->name('update');
    });
});
