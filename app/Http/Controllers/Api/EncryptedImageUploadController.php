<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EncryptedImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'session_id' => 'required|string',
                'face_image' => 'required|file|mimes:jpg,jpeg|max:10240',
                'frames.*' => 'file|mimes:jpg,jpeg|max: 5120',
            ]);

            $sessionId = $request->input('session_id');
            $baseDir = "encrypted_attendance/{$sessionId}";

            Log::info("📥 Receiving upload", ['session' => $sessionId]);

            // =====================================
            // ENKRIPSI FACE IMAGE
            // =====================================
            $facePath = null;

            if ($request->hasFile('face_image')) {
                $faceFile = $request->file('face_image');

                // STEP 1: Baca isi file (binary data)
                $faceData = file_get_contents($faceFile->getRealPath());

                Log::info("📷 Face image size", ['bytes' => strlen($faceData)]);

                // STEP 2: ENCRYPT dengan APP_KEY
                $encryptedFace = Crypt::encryptString($faceData);

                Log::info("🔒 Face encrypted", ['encrypted_size' => strlen($encryptedFace)]);

                // STEP 3: Save ke storage
                $facePath = "{$baseDir}/face.enc";
                Storage::disk('private')->put($facePath, $encryptedFace);

                Log::info("✅ Face saved", ['path' => $facePath]);
            }

            // =====================================
            // ENKRIPSI FRAMES
            // =====================================
            $framesPath = null;
            $framesCount = 0;   

            if ($request->hasFile('frames')) {
                $framesDir = "{$baseDir}/frames";
                $frames = $request->file('frames');

                foreach ($frames as $index => $frameFile) {
                    // STEP 1: Baca file
                    $frameData = file_get_contents($frameFile->getRealPath());

                    // STEP 2: ENCRYPT
                    $encryptedFrame = Crypt::encryptString($frameData);

                    // STEP 3: Save dengan nama asli (tapi . enc)
                    $originalName = $frameFile->getClientOriginalName();
                    $frameName = str_replace('.jpg', '.enc', $originalName);

                    $framePath = "{$framesDir}/{$frameName}";
                    Storage::disk('private')->put($framePath, $encryptedFrame);

                    $framesCount++;
                }

                $framesPath = $framesDir;

                Log::info("✅ Frames encrypted", ['count' => $framesCount]);
            }

            // Return paths ke Python
            return response()->json([
                'success' => true,
                'message' => 'Images encrypted successfully',
                'face_path' => $facePath,
                'frames_path' => $framesPath,
                'frames_count' => $framesCount,
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Upload error", ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
