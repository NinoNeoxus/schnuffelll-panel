<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\EggController;
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('/', [AuthController::class, 'login'])->name('login');
Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Servers
        Route::resource('servers', ServerController::class);
        
        // Nodes
        Route::resource('nodes', NodeController::class);
        Route::get('/nodes/{node}/configuration', [NodeController::class, 'configuration'])->name('nodes.configuration');
        Route::get('/nodes/{node}/configuration.yml', [NodeController::class, 'configurationYaml'])->name('nodes.configuration.yaml');
        Route::post('/nodes/{node}/reset-token', [NodeController::class, 'resetToken'])->name('nodes.reset-token');

        
        // Users
        Route::resource('users', UserController::class);
        
        // Locations
        Route::resource('locations', LocationController::class);
        
        // Eggs
        Route::resource('eggs', EggController::class);
        Route::post('/eggs/import', [EggController::class, 'import'])->name('eggs.import');
        Route::get('/eggs/{egg}/export', [EggController::class, 'export'])->name('eggs.export');
        
        // Backups
        Route::resource('backups', BackupController::class)->only(['index', 'store', 'destroy']);
        Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        
        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    });
});

