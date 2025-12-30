<?php

use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\SecureImageController;
use Illuminate\Support\Facades\Route;
	
// Redirect halaman utama ke admin panel
Route::get('/', function () {
    return redirect('/admin');
});

// Halaman face recognition (tidak lagi jadi landing page)
Route::get('/face-recognition', [FaceRecognitionController::class, 'index'])->name('face.home');

// Source image routes
Route::middleware('auth')->group(function () {
    Route::get('/secure-image/face/{session_id}', [SecureImageController::class, 'showFace'])
        ->name('secure.image.face');

    Route::get('/secure-image/frame/{session_id}/{frame_number}', [SecureImageController::class, 'showFrame'])
        ->name('secure.image.frame');

    Route::get('/secure-image/frames-list/{session_id}', [SecureImageController::class, 'getFramesList'])
        ->name('secure.image.frames.list');
});
