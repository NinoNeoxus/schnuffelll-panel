<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeController;
use App\Models\Server;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// The "Auto Green" Route
Route::get('/node/diagnose/{token}', [NodeController::class, 'diagnose']);

// Daemon Configuration Route (Daemon calls this to get required config for a server)
Route::get('/node/configuration', function (Request $request) {
    return response()->json([
        'debug' => false,
        'uuid' => 'mock-uuid',
        'token_id' => 'mock-token-id',
        'token' => 'mock-token',
        'api' => [
            'host' => '0.0.0.0',
            'port' => 8080,
            'ssl' => [
                'enabled' => false,
            ],
            'upload_limit' => 100,
        ],
        'system' => [
            'data' => '/var/lib/pterodactyl/volumes',
            'sftp' => [
                'bind_port' => 2022,
            ],
        ],
        'remote' => 'https://panel.example.com',
    ]);
});

// Status updates from Wings
Route::post('/servers/{server}/status', function (Server $server, Request $request) {
    // Validate request...
    // Update server status
    return response()->json(['success' => true]);
});
