<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Berhasil - Sistem Absensi Face Recognition</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Success Icon -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Registrasi Berhasil!
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Akun Anda telah berhasil dibuat
                </p>
            </div>

            <!-- Info Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-8">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Menunggu Persetujuan Administrator
                                </h3>
                                <div class="mt-2 text-sm text-gray-600">
                                    <p>Akun Anda saat ini dalam status <strong>pending</strong> dan memerlukan persetujuan dari administrator sebelum dapat digunakan.</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Langkah Selanjutnya:</h4>
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
                                <li>Administrator akan meninjau dan memverifikasi akun Anda</li>
                                <li>Setelah disetujui, Anda dapat login ke Portal Guru</li>
                                <li>Administrator akan mengassign kelas yang akan Anda ajar</li>
                            </ol>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Informasi:</h4>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                                <li>Proses verifikasi biasanya memakan waktu 1-2 hari kerja</li>
                                <li>Jika ada pertanyaan, silakan hubungi administrator sekolah</li>
                                <li>Simpan email yang Anda gunakan untuk login</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col space-y-3">
                <a href="/admin/login" 
                   class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    Kembali ke Halaman Login
                </a>
                <a href="/" 
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    Kembali ke Beranda
                </a>
            </div>

            <!-- Contact Info -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-600 text-center">
                    Butuh bantuan? Hubungi administrator di <a href="mailto:admin@sekolah.com" class="text-emerald-600 hover:text-emerald-500">admin@sekolah.com</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
