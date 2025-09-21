<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Test routes
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
        'auth_check' => auth()->check(),
        'auth_id' => auth()->id(),
        'session_data' => session()->all(),
        'timestamp' => now()->toISOString(),
    ]);
})->middleware('auth:sanctum');

// Public API routes (no authentication required)
Route::prefix('files')->group(function () {
    // Public file operations
    Route::get('/public/{shareId}', [FileController::class, 'showPublic'])->name('api.files.show.public');
    Route::get('/download/{shareId}', [FileController::class, 'download'])->name('api.files.download');
});

// Private API routes (authentication required)
Route::prefix('files')->group(function () {
    // Private file operations
    Route::post('/', [FileController::class, 'store'])->name('api.files.store');
    Route::get('/', [FileController::class, 'index'])->name('api.files.index');
    Route::get('/{file}', [FileController::class, 'show'])->name('api.files.show');
    Route::put('/{file}', [FileController::class, 'update'])->name('api.files.update');
    Route::delete('/{file}', [FileController::class, 'destroy'])->name('api.files.destroy');
    Route::post('/{file}/toggle-visibility', [FileController::class, 'toggleVisibility'])->name('api.files.toggle-visibility');
})->middleware('auth:sanctum');
