<?php

use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\SecureImageController;
use Illuminate\Support\Facades\Route;

// halaman utama
Route::get('/', [FaceRecognitionController::class, 'index'])->name('home');

// training routes
Route::get('/train', [FaceRecognitionController::class, 'trainPage'])->name('train');
Route::post('/train', [FaceRecognitionController::class, 'train'])->name('train.submit');

// detect routes
Route::get('/detect', [FaceRecognitionController::class, 'detectPage'])->name('detect');
Route::post('/detect', [FaceRecognitionController::class, 'detect'])->name('detect.submit');

// live camera route
Route::get('/live', [FaceRecognitionController::class, 'livePage'])->name('live');

// delete face
Route::delete('/delete/{name}', [FaceRecognitionController::class, 'delete'])->name('delete');

// Source image routes
Route::middleware('auth')->group(function () {
    Route::get('/secure-image/face/{session_id}', [SecureImageController::class, 'showFace'])
        ->name('secure.image.face');

    Route::get('/secure-image/frame/{session_id}/{frame_number}', [SecureImageController::class, 'showFrame'])
        ->name('secure.image.frame');

    Route::get('/secure-image/frames-list/{session_id}', [SecureImageController::class, 'getFramesList'])
        ->name('secure.image.frames.list');
});
