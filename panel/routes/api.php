<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeController;

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

// Daemon Configuration Route (Daemon calls this to get its config)
Route::get('/node/configuration', function (Request $request) {
    // Validate token header...
    return response()->json(['config' => '...']);
});
