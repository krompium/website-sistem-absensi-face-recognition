<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\Auth\RegisterController;
	
// Redirect halaman utama ke admin panel
Route::get('/', function () {
    return redirect('/admin');
});

// Guru Registration Routes
Route::get('/register/guru', [RegisterController::class, 'showRegistrationForm'])->name('register.guru');
Route::post('/register/guru', [RegisterController::class, 'register'])->name('register.guru.submit');
Route::get('/register/guru/success', [RegisterController::class, 'success'])->name('register.guru.success');

// Halaman face recognition (tidak lagi jadi landing page)
Route::get('/face-recognition', [FaceRecognitionController::class, 'index'])->name('face.home');

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
