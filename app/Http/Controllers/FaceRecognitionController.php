<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionController extends Controller
{
    // url python api
    private $apiUrl = 'http://127.0.0.1:5000/api';

    // halaman utama
    public function index()
    {
        // ambil list wajah tersimpan
        try {
            $response = Http::get("{$this->apiUrl}/faces");
            $faces = $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            $faces = null;
        }

        return view('face-recognition.index', compact('faces'));
    }

    // halaman training
    public function trainPage()
    {
        return view('face-recognition.train');
    }

    // proses upload foto training
    public function train(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $response = Http::attach(
                'image',
                file_get_contents($request->file('image')->getRealPath()),
                $request->file('image')->getClientOriginalName()
            )->post("{$this->apiUrl}/train", [
                'name' => $request->name
            ]);

            if ($response->successful()) {
                return back()->with('success', $response->json()['message']);
            } else {
                return back()->with('error', $response->json()['error'] ?? 'gagal upload foto');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'koneksi ke python api gagal: ' . $e->getMessage());
        }
    }

    // halaman deteksi
    public function detectPage()
    {
        return view('face-recognition.detect');
    }

    // proses deteksi dari upload foto
    public function detect(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $response = Http::attach(
                'image',
                file_get_contents($request->file('image')->getRealPath()),
                $request->file('image')->getClientOriginalName()
            )->post("{$this->apiUrl}/detect");

            if ($response->successful()) {
                return back()->with([
                    'success' => 'deteksi berhasil',
                    'result' => $response->json()
                ]);
            } else {
                return back()->with('error', $response->json()['error'] ?? 'gagal deteksi foto');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'koneksi ke python api gagal: ' . $e->getMessage());
        }
    }

    // halaman live camera
    public function livePage()
    {
        return view('face-recognition.live');
    }

    // hapus data wajah
    public function delete($name)
    {
        try {
            $response = Http::delete("{$this->apiUrl}/delete/{$name}");

            if ($response->successful()) {
                return back()->with('success', $response->json()['message']);
            } else {
                return back()->with('error', $response->json()['error'] ?? 'gagal hapus data');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'koneksi ke python api gagal: ' . $e->getMessage());
        }
    }

    // api get list faces (untuk ajax)
    public function getFaces()
    {
        try {
            $response = Http::get("{$this->apiUrl}/faces");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
