<?php
namespace App\Http\Controllers;

use App\Models\IndikasiSiswa;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SecureImageController extends Controller
{
    public function showFace($session_id)
    {
        if (! auth()->check()) {
            abort(403, 'Please login first');
        }

        try {
            $indikasi = IndikasiSiswa::where('session_id', $session_id)->firstOrFail();

            if (! $indikasi->face_image) {
                abort(404, 'No face image');
            }

            $encryptedPath = $indikasi->face_image;

            if (! Storage::disk('private')->exists($encryptedPath)) {
                Log::error("Face image not found:  {$encryptedPath}");
                abort(404, 'File not found');
            }

            $encryptedData  = Storage::disk('private')->get($encryptedPath);
            $decryptedImage = Crypt::decryptString($encryptedData);

            return response($decryptedImage)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'private, max-age=3600');

        } catch (\Exception $e) {
            Log::error("Decrypt face error: " . $e->getMessage());
            abort(500, 'Cannot decrypt image');
        }
    }

    public function showFrame($session_id, $frame_number)
    {
        if (! auth()->check()) {
            abort(403);
        }

        try {
            $indikasi = IndikasiSiswa::where('session_id', $session_id)->firstOrFail();

            // ✅ Coba beberapa kemungkinan path
            $possiblePaths = [
                // Path 1: encrypted_attendance/session_xxx/frames/frame_0001.enc
                "encrypted_attendance/{$session_id}/frames/frame_{$frame_number}.enc",

                // Path 2: Menggunakan frames_dir dari database
                $indikasi->frames_dir ? "encrypted_attendance/{$indikasi->frames_dir}/frame_{$frame_number}.enc" : null,

                // Path 3: Langsung di frames_dir
                $indikasi->frames_dir ? "{$indikasi->frames_dir}/frame_{$frame_number}. enc" : null,

                // Path 4: Di root encrypted_attendance
                "encrypted_attendance/frame_{$frame_number}.enc",
            ];

            $framePath = null;
            foreach ($possiblePaths as $path) {
                if ($path && Storage::disk('private')->exists($path)) {
                    $framePath = $path;
                    break;
                }
            }

            if (! $framePath) {
                Log::error("Frame not found.  Session: {$session_id}, Frame: {$frame_number}, Tried paths: " . json_encode($possiblePaths));
                abort(404, 'Frame not found');
            }

            $encryptedData  = Storage::disk('private')->get($framePath);
            $decryptedImage = Crypt::decryptString($encryptedData);

            return response($decryptedImage)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'private, max-age=3600');

        } catch (\Exception $e) {
            Log::error("Decrypt frame error: " . $e->getMessage());
            abort(500, 'Cannot decrypt frame');
        }
    }
}
