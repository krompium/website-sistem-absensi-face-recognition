<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\FaceRecognitionController;
use App\Http\Controllers\Api\EncryptedImageUploadController;

/*
|--------------------------------------------------------------------------
| API Routes untuk IoT Integration
|--------------------------------------------------------------------------
*/

// Public routes (dengan API key authentication)
Route::middleware(['api.key'])->group(function () {

    // Face Recognition & Verification
    Route::prefix('face')->group(function () {
        Route::post('/verify', [FaceRecognitionController::class, 'verify']);
        Route::post('/register', [FaceRecognitionController::class, 'register']);
        Route::get('/student/{nis}', [FaceRecognitionController::class, 'getStudentByNIS']);
    });

    // Attendance Management
    Route::prefix('attendance')->group(function () {
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/today', [AttendanceController::class, 'todayAttendances']);
        Route::get('/student/{studentId}/latest', [AttendanceController::class, 'latestAttendance']);
    });

    // Student Data
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::get('/nis/{nis}', [StudentController::class, 'getByNIS']);
    });

    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    });
    
    // Encrypted Image Upload
     Route::post('/attendance/upload-images', [
        EncryptedImageUploadController::class,
        'upload'
    ]);
});

// Protected routes (dengan Sanctum untuk admin/web app)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});