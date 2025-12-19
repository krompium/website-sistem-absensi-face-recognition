@extends('layouts.app')

@section('title', 'training - face recognition')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-upload text-blue-600"></i> upload foto training
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- upload dari file -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">
                    <i class="fas fa-file-upload mr-2"></i>upload dari file
                </h2>

                <form action="{{ route('train.submit') }}" method="POST" enctype="multipart/form-data" id="trainForm">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-user mr-2"></i>nama orang
                        </label>
                        <input type="text" name="name" id="name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="contoh: john doe" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-image mr-2"></i>foto wajah
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                            <input type="file" name="image" id="image" class="hidden"
                                accept="image/jpeg,image/png,image/jpg" onchange="previewImageFile(event)">
                            <label for="image" class="cursor-pointer">
                                <div id="preview-file-container" class="mb-3 hidden">
                                    <img id="preview-file" class="mx-auto max-h-48 rounded-lg" src=""
                                        alt="preview">
                                </div>
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600 text-sm">klik untuk upload foto</p>
                                <p class="text-xs text-gray-500 mt-1">jpg, jpeg, png (max 5mb)</p>
                            </label>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition"
                        id="submitBtn">
                        <i class="fas fa-upload mr-2"></i>upload dan training
                    </button>
                </form>
            </div>

            <!-- capture dari webcam -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">
                    <i class="fas fa-camera mr-2"></i>capture dari webcam
                </h2>

                <div class="mb-4">
                    <label for="name-webcam" class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-user mr-2"></i>nama orang
                    </label>
                    <input type="text" id="name-webcam"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="contoh: john doe" required>
                </div>

                <!-- webcam preview -->
                <div class="mb-4 bg-gray-900 rounded-lg overflow-hidden relative" style="height: 240px;">
                    <video id="webcam" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden"></canvas>
                    <div id="webcam-placeholder" class="absolute inset-0 flex items-center justify-center text-gray-400">
                        <div class="text-center">
                            <i class="fas fa-video-slash text-4xl mb-2"></i>
                            <p class="text-sm">klik tombol dibawah untuk aktifkan kamera</p>
                        </div>
                    </div>
                </div>

                <!-- captured photo preview -->
                <div id="captured-preview-container" class="mb-4 hidden">
                    <img id="captured-preview" class="w-full rounded-lg" src="" alt="captured">
                </div>

                <!-- control buttons -->
                <div class="space-y-2">
                    <button id="start-camera-btn"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg transition"
                        onclick="startCamera()">
                        <i class="fas fa-video mr-2"></i>aktifkan kamera
                    </button>

                    <button id="capture-btn"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 rounded-lg transition hidden"
                        onclick="capturePhoto()">
                        <i class="fas fa-camera mr-2"></i>ambil foto
                    </button>

                    <button id="retake-btn"
                        class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 rounded-lg transition hidden"
                        onclick="retakePhoto()">
                        <i class="fas fa-redo mr-2"></i>foto ulang
                    </button>

                    <button id="upload-captured-btn"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition hidden"
                        onclick="uploadCaptured()">
                        <i class="fas fa-upload mr-2"></i>upload foto ini
                    </button>
                </div>
            </div>
        </div>

        <!-- tips -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <h3 class="font-medium text-blue-900 mb-2">
                <i class="fas fa-lightbulb mr-2"></i>tips foto yang baik:
            </h3>
            <ul class="text-sm text-blue-800 space-y-1 ml-6 list-disc">
                <li>gunakan foto dengan 1 wajah saja</li>
                <li>wajah menghadap ke kamera</li>
                <li>pencahayaan yang cukup</li>
                <li>foto tidak blur atau buram</li>
                <li>upload beberapa foto dari angle berbeda untuk akurasi lebih baik</li>
            </ul>
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
        let stream = null;
        let capturedImage = null;

        // preview image dari file upload
        function previewImageFile(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-file').src = e.target.result;
                    document.getElementById('preview-file-container').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        // aktifkan kamera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: {
                            ideal: 640
                        },
                        height: {
                            ideal: 480
                        }
                    }
                });

                const video = document.getElementById('webcam');
                video.srcObject = stream;

                document.getElementById('webcam-placeholder').classList.add('hidden');
                document.getElementById('start-camera-btn').classList.add('hidden');
                document.getElementById('capture-btn').classList.remove('hidden');

            } catch (error) {
                alert(
                    'gagal mengakses kamera!\npastikan:\n1. kamera sudah terhubung\n2. browser memiliki izin akses kamera\n3. tidak ada aplikasi lain yang menggunakan kamera');
                console.error('error:', error);
            }
        }

        // ambil foto dari webcam
        function capturePhoto() {
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');

            // set canvas size sama dengan video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // gambar video frame ke canvas
            ctx.drawImage(video, 0, 0);

            // convert canvas ke base64
            capturedImage = canvas.toDataURL('image/jpeg');

            // tampilkan preview
            document.getElementById('captured-preview').src = capturedImage;
            document.getElementById('captured-preview-container').classList.remove('hidden');

            // hide video, show buttons
            video.classList.add('hidden');
            document.getElementById('capture-btn').classList.add('hidden');
            document.getElementById('retake-btn').classList.remove('hidden');
            document.getElementById('upload-captured-btn').classList.remove('hidden');

            // stop kamera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        }

        // foto ulang
        function retakePhoto() {
            const video = document.getElementById('webcam');

            // reset preview
            document.getElementById('captured-preview-container').classList.add('hidden');
            capturedImage = null;

            // show video kembali
            video.classList.remove('hidden');
            document.getElementById('retake-btn').classList.add('hidden');
            document.getElementById('upload-captured-btn').classList.add('hidden');

            // aktifkan kamera lagi
            startCamera();
        }

        // upload foto yang sudah di-capture
        async function uploadCaptured() {
            const name = document.getElementById('name-webcam').value.trim();

            if (!name) {
                alert('nama harus diisi!');
                document.getElementById('name-webcam').focus();
                return;
            }

            if (!capturedImage) {
                alert('belum ada foto yang di-capture!');
                return;
            }

            // disable button
            const btn = document.getElementById('upload-captured-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>sedang mengupload...';

            try {
                // convert base64 ke blob
                const response = await fetch(capturedImage);
                const blob = await response.blob();

                // buat form data
                const formData = new FormData();
                formData.append('name', name);
                formData.append('image', blob, 'webcam-capture.jpg');

                // kirim ke server
                const uploadResponse = await fetch('{{ route('train.submit') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                // cek apakah response adalah json
                const contentType = uploadResponse.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await uploadResponse.text();
                    console.error('server response:', textResponse);
                    throw new Error(
                        'server tidak mengembalikan JSON. pastikan python api sudah berjalan di http://127.0.0.1:5000'
                        );
                }

                const result = await uploadResponse.json();

                if (uploadResponse.ok && result.success) {
                    alert('foto berhasil diupload dan training!\n' + (result.message || 'sukses'));
                    // reload halaman
                    window.location.reload();
                } else {
                    alert('error: ' + (result.error || 'gagal upload foto'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-upload mr-2"></i>upload foto ini';
                }

            } catch (error) {
                console.error('upload error:', error);
                alert('terjadi kesalahan saat upload:\n' + error.message + '\n\npastikan python api sudah berjalan!');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload mr-2"></i>upload foto ini';
            }
        }

        // loading state untuk form upload file
        document.getElementById('trainForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>sedang memproses...';
        });

        // cleanup saat halaman ditutup
        window.addEventListener('beforeunload', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
@endpush
