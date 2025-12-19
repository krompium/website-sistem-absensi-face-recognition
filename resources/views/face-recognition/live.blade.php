@extends('layouts.app')

@section('title', 'live camera - face recognition')

@section('content')
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-video text-purple-600"></i> live camera detection
        </h1>

        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- status indicator -->
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center">
                    <div id="status-indicator" class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
                    <span id="status-text" class="text-gray-600">menghubungkan ke kamera...</span>
                </div>
                <button id="toggle-stream" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded"
                    onclick="toggleStream()">
                    <i class="fas fa-play mr-2"></i>mulai
                </button>
            </div>

            <!-- video stream container -->
            <div class="bg-gray-900 rounded-lg overflow-hidden relative">
                <img id="video-stream" src="" class="w-full h-auto" style="display: none;" alt="live stream">
                <div id="placeholder" class="text-center py-32 text-gray-400">
                    <i class="fas fa-video-slash text-6xl mb-3"></i>
                    <p>klik tombol 'mulai' untuk memulai deteksi real-time</p>
                </div>
            </div>

            <!-- info -->
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>informasi:
                </h3>
                <ul class="text-sm text-blue-800 space-y-1 ml-6 list-disc">
                    <li>pastikan python api sudah berjalan di http://127.0.0.1:5000</li>
                    <li>pastikan webcam sudah terhubung dan diizinkan oleh browser</li>
                    <li>wajah yang terdeteksi akan ditandai dengan kotak dan nama</li>
                    <li>wajah yang tidak dikenali akan ditandai dengan 'unknown'</li>
                </ul>
            </div>
        </div>

        <!-- back button -->
        <div class="mt-6">
            <a href="{{ route('home') }}" class="inline-block bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                <i class="fas fa-arrow-left mr-2"></i>kembali
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let isStreaming = false;
        const streamUrl = 'http://127.0.0.1:5000/api/video';
        const videoElement = document.getElementById('video-stream');
        const placeholder = document.getElementById('placeholder');
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const toggleBtn = document.getElementById('toggle-stream');

        function toggleStream() {
            if (isStreaming) {
                stopStream();
            } else {
                startStream();
            }
        }

        function startStream() {
            videoElement.src = streamUrl;
            videoElement.style.display = 'block';
            placeholder.style.display = 'none';

            isStreaming = true;
            statusIndicator.classList.remove('bg-red-500');
            statusIndicator.classList.add('bg-green-500');
            statusText.textContent = 'streaming aktif';
            toggleBtn.innerHTML = '<i class="fas fa-stop mr-2"></i>stop';
            toggleBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
            toggleBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        }

        function stopStream() {
            videoElement.src = '';
            videoElement.style.display = 'none';
            placeholder.style.display = 'block';

            isStreaming = false;
            statusIndicator.classList.remove('bg-green-500');
            statusIndicator.classList.add('bg-red-500');
            statusText.textContent = 'streaming berhenti';
            toggleBtn.innerHTML = '<i class="fas fa-play mr-2"></i>mulai';
            toggleBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
            toggleBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
        }

        // handle error saat stream tidak bisa dimuat
        videoElement.onerror = function() {
            stopStream();
            statusText.textContent = 'gagal terhubung ke kamera (pastikan python api berjalan)';
            alert(
                'gagal terhubung ke kamera!\npastikan:\n1. python api sudah berjalan\n2. webcam sudah terhubung\n3. tidak ada aplikasi lain yang menggunakan webcam'
            );
        };
    </script>
@endpush
