<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/share/{shareId}', function ($shareId) {
    return Inertia::render('Share', [
        'shareId' => $shareId
    ]);
})->name('public.share');

Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('files', function () {
    return Inertia::render('files/List');
})->middleware(['auth', 'verified'])->name('files.index');

// File Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('files', \App\Http\Controllers\FileController::class)->except(['index']);
    Route::post('files/{file}/toggle-visibility', [\App\Http\Controllers\FileController::class, 'toggleVisibility'])->name('files.toggle-visibility');
    Route::get('files/{file}/share-link', [\App\Http\Controllers\FileController::class, 'getShareLink'])->name('files.share-link');
});

// User Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
