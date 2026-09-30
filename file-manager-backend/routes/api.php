<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto']);

    Route::get('/files/stats', [FileController::class, 'stats']);
    Route::get('/files/trash', [FileController::class, 'trash']);
    Route::get('/files/shared-with-me', [FileController::class, 'sharedWithMe']);
    Route::get('/files/starred', [FileController::class, 'starred']);
    Route::get('/files/search', [FileController::class, 'search']);
    Route::post('/files/{id}/restore', [FileController::class, 'restore']);
    Route::delete('/files/{id}/force', [FileController::class, 'forceDelete']);
    Route::get('/files/{id}/download', [FileController::class, 'download']);
    Route::post('/files/{id}/share', [FileController::class, 'share']);
    Route::delete('/files/{id}/share/{userId}', [FileController::class, 'unshare']);
    Route::get('/files/{id}/shares', [FileController::class, 'shares']);
    Route::post('/files/{id}/star', [FileController::class, 'star']);
    Route::delete('/files/{id}/star', [FileController::class, 'unstar']);
    Route::apiResource('files', FileController::class);
});
