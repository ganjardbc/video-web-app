<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now()->toISOString(),
    ]);
});

Route::get('/test-auth', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Authenticated API is working!',
        'user' => $request->user(),
        'timestamp' => now()->toISOString(),
    ]);
})->middleware('auth:sanctum');
