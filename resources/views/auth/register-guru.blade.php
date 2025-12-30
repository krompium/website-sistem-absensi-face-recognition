<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi Guru - Sistem Absensi Face Recognition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Registrasi Akun Guru
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Daftar untuk mendapatkan akses ke Portal Guru
                </p>
            </div>

            <!-- Registration Form -->
            <form class="mt-8 space-y-6" action="{{ route('register.guru.submit') }}" method="POST" x-data="registrationForm">
                @csrf

                <div class="rounded-md shadow-sm space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="name" 
                            name="name" 
                            type="text" 
                            required 
                            value="{{ old('name') }}"
                            class="mt-1 appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm @error('name') border-red-500 @enderror"
                            placeholder="Masukkan nama lengkap">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            value="{{ old('email') }}"
                            class="mt-1 appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                            placeholder="nama@email.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            x-model="password"
                            class="mt-1 appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                            placeholder="Minimal 8 karakter">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            required 
                            x-model="passwordConfirmation"
                            class="mt-1 appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                            placeholder="Ulangi password">
                        <p x-show="!passwordsMatch && passwordConfirmation.length > 0" 
                           class="mt-1 text-sm text-red-600">
                            Password tidak cocok
                        </p>
                    </div>
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        x-bind:disabled="!passwordsMatch || password.length < 8">
                        Daftar
                    </button>
                    <p x-show="!passwordsMatch && passwordConfirmation.length > 0" 
                       class="mt-2 text-sm text-red-600 text-center">
                        ⚠️ Password tidak cocok. Mohon periksa kembali.
                    </p>
                    <p x-show="password.length > 0 && password.length < 8" 
                       class="mt-2 text-sm text-red-600 text-center">
                        ⚠️ Password minimal 8 karakter.
                    </p>
                </div>

                <div class="text-center">
                    <a href="/admin/login" class="text-sm text-emerald-600 hover:text-emerald-500">
                        Sudah punya akun? Login di sini
                    </a>
                </div>
            </form>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Informasi Penting</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Akun akan diverifikasi oleh administrator</li>
                                <li>Anda akan menerima notifikasi setelah akun disetujui</li>
                                <li>Gunakan email aktif untuk registrasi</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('registrationForm', () => ({
                password: '',
                passwordConfirmation: '',
                
                get passwordsMatch() {
                    if (this.passwordConfirmation.length === 0) {
                        return true;
                    }
                    return this.password === this.passwordConfirmation;
                }
            }))
        })
    </script>
</body>
</html>
