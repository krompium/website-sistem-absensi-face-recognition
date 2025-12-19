<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceRecognitionController;
	
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
