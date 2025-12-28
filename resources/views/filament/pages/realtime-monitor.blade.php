{{-- resources/views/filament/pages/realtime-monitor.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-funnel class="w-5 h-5" />
                    <span>Filter Data</span>
                </div>
            </x-slot>
            
            {{ $this->form }}
        </x-filament::section>

        {{-- Statistics Cards --}}
        @php
            $stats = $this->getStats();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Total Absensi --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Absensi</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">dari {{ $stats['total_siswa'] }} siswa</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                        <x-heroicon-o-users class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            {{-- Tepat Waktu --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tepat Waktu</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['tepat_waktu'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">< 07:30</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            {{-- Terlambat --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Terlambat</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['terlambat'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">> 07:30</p>
                    </div>
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                        <x-heroicon-o-clock class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                    </div>
                </div>
            </div>

            {{-- Tidak Hadir (Izin + Sakit + Alpa) --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak Hadir</p>
                        <p class="text-3xl font-bold text-red-600">{{ $stats['tidak_hadir'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Izin/Sakit/Alpa</p>
                    </div>
                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                        <x-heroicon-o-x-circle class="w-8 h-8 text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </div>

            {{-- Belum Absen --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Belum Absen</p>
                        <p class="text-3xl font-bold text-gray-600">{{ $stats['belum_absen'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">siswa</p>
                    </div>
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                        <x-heroicon-o-question-mark-circle class="w-8 h-8 text-gray-600 dark:text-gray-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Stats Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gray-200 dark:bg-gray-700 rounded-lg">
                        <x-heroicon-o-information-circle class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Izin</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['izin'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-200 dark:bg-purple-700 rounded-lg">
                        <x-heroicon-o-heart class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <p class="text-xs text-purple-600 dark:text-purple-400">Sakit</p>
                        <p class="text-xl font-bold text-purple-900 dark:text-purple-100">{{ $stats['sakit'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-200 dark:bg-red-700 rounded-lg">
                        <x-heroicon-o-exclamation-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-xs text-red-600 dark:text-red-400">Alpa</p>
                        <p class="text-xl font-bold text-red-900 dark:text-red-100">{{ $stats['alpa'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Rate Progress --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat Kehadiran Hari Ini</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $stats['percentage'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                <div class="h-4 rounded-full transition-all duration-500 {{ $stats['percentage'] >= 80 ? 'bg-green-600' : ($stats['percentage'] >= 60 ? 'bg-yellow-600' : 'bg-red-600') }}" 
                     style="width: {{ $stats['percentage'] }}%">
                </div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-600 dark:text-gray-400">
                <span>0%</span>
                <span class="font-medium">
                    @if($stats['percentage'] >= 80)
                        ✅ Sangat Baik
                    @elseif($stats['percentage'] >= 60)
                        ⚠️ Cukup
                    @else
                        ❌ Kurang
                    @endif
                </span>
                <span>100%</span>
            </div>
        </div>

        {{-- Live Attendance List --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <span>Data Absensi Real-time</span>
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                    </div>
                    <span class="text-sm text-gray-500">
                        Last update: {{ now()->format('H:i:s') }}
                    </span>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indikasi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                        @forelse($this->getAttendanceData() as $absensi)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                {{-- Waktu --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                        {{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') }}
                                    </div>
                                    @if($this->isLate($absensi->jam_masuk))
                                        <span class="text-xs text-yellow-600">⚠️ Terlambat</span>
                                    @endif
                                </td>

                                {{-- Kode Siswa --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-mono text-gray-900 dark:text-gray-100">
                                            {{ $absensi->kode_siswa }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Nama Siswa --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $absensi->siswa->nama_siswa ?? '-' }}
                                </td>

                                {{-- Kelas --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $absensi->siswa->kelas->getFullName() ?? $absensi->id_kelas }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColor = $this->getStatusColor($absensi->status);
                                        $statusLabel = $this->getStatusLabel($absensi->status);
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 
                                        dark:bg-{{ $statusColor }}-900 dark:text-{{ $statusColor }}-200">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                {{-- Keterangan Waktu --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($absensi->jam_keluar)
                                        <span class="text-green-600">✓ Sudah pulang</span>
                                        <br>
                                        <span class="text-xs">{{ \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i') }}</span>
                                    @else
                                        <span class="text-gray-400">- Belum pulang</span>
                                    @endif
                                </td>

                                {{-- Indikasi Mabuk --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $hasIndikasi = $absensi->indikasiSiswa()->where('final_decision', 'MABUK')->exists();
                                    @endphp
                                    @if($hasIndikasi)
                                        <span class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-bold">
                                            ⚠️ TERDETEKSI
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-4 text-gray-400" />
                                    <p class="font-medium">Belum ada data absensi untuk hari ini</p>
                                    <p class="text-sm text-gray-400 mt-1">Data akan muncul secara real-time saat ada absensi masuk</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Record Count --}}
            @if($this->getAttendanceData()->count() > 0)
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 text-center">
                    Menampilkan {{ $this->getAttendanceData()->count() }} data absensi
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Auto-refresh every 10 seconds --}}
    <script>
        setInterval(function() {
            @this.call('$refresh');
        }, 10000); // Refresh every 10 seconds using Livewire
    </script>
</x-filament-panels::page>