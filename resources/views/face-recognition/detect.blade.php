@extends('layouts.app')

@section('title', 'deteksi - face recognition')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-search text-green-600"></i> deteksi wajah dari foto
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- form upload -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">upload foto</h2>

                <form action="{{ route('detect.submit') }}" method="POST" enctype="multipart/form-data" id="detectForm">
                    @csrf

                    <div class="mb-6">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input type="file" name="image" id="image" class="hidden"
                                accept="image/jpeg,image/png,image/jpg" required onchange="previewImage(event)">
                            <label for="image" class="cursor-pointer">
                                <div id="preview-container" class="mb-4 hidden">
                                    <img id="preview" class="mx-auto max-h-64 rounded-lg" src="" alt="preview">
                                </div>
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-600">klik untuk upload foto</p>
                                <p class="text-sm text-gray-500 mt-1">format: jpg, jpeg, png (max 5mb)</p>
                            </label>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg transition"
                        id="submitBtn">
                        <i class="fas fa-search mr-2"></i>deteksi wajah
                    </button>
                </form>
            </div>

            <!-- hasil deteksi -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">hasil deteksi</h2>

                @if (session('result'))
                    <div class="mb-4">
                        <img src="{{ session('result')['image'] }}" class="w-full rounded-lg shadow-md" alt="hasil deteksi">
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-2">
                            <i class="fas fa-user-friends mr-2"></i>
                            <strong>wajah terdeteksi:</strong> {{ session('result')['faces_detected'] }}
                        </p>

                        @if (count(session('result')['faces']) > 0)
                            <div class="mt-3 space-y-2">
                                @foreach (session('result')['faces'] as $index => $face)
                                    <div
                                        class="bg-white rounded-lg p-3 border {{ $face['name'] == 'unknown' ? 'border-red-200' : 'border-green-200' }}">
                                        <p
                                            class="font-medium {{ $face['name'] == 'unknown' ? 'text-red-600' : 'text-green-600' }}">
                                            <i
                                                class="fas {{ $face['name'] == 'unknown' ? 'fa-user-slash' : 'fa-user-check' }} mr-2"></i>
                                            {{ $face['name'] }}
                                        </p>
                                        @if ($face['name'] != 'unknown')
                                            <p class="text-sm text-gray-500">
                                                tingkat kepercayaan: {{ $face['confidence'] }}%
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center text-gray-400 py-12">
                        <i class="fas fa-image text-6xl mb-3"></i>
                        <p>hasil deteksi akan muncul di sini</p>
                    </div>
                @endif
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
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('preview-container').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        // loading state saat submit
        document.getElementById('detectForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>sedang mendeteksi...';
        });
    </script>
@endpush
