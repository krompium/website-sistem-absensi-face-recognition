{{-- resources/views/filament/pages/reports.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Report Form --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-chart-bar class="w-6 h-6" />
                    <span>Generate Laporan</span>
                </div>
            </x-slot>
            
            <x-slot name="description">
                Pilih jenis laporan dan periode waktu untuk generate laporan
            </x-slot>

            <div class="space-y-4">
                {{ $this->form }}

                <div class="flex gap-2 justify-end">
                    <x-filament::button 
                        wire:click="generateReport"
                        color="primary"
                        icon="heroicon-o-document-arrow-down">
                        Generate Laporan
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Preview Statistics --}}
        @php
            $preview = $this->getReportPreview();
        @endphp

        @if($preview)
            <x-filament::section>
                <x-slot name="heading">
                    Preview Data Laporan
                </x-slot>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {{-- Total Records --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                        <p class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">Total Records</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $preview['total_records'] ?? 0 }}</p>
                    </div>

                    {{-- Hadir --}}
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">Hadir</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $preview['hadir'] ?? 0 }}</p>
                        @if(($preview['total_records'] ?? 0) > 0)
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                {{ $preview['percentage']['hadir'] ?? 0 }}%
                            </p>
                        @endif
                    </div>

                    {{-- Terlambat --}}
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
                        <p class="text-sm text-yellow-600 dark:text-yellow-400 font-medium mb-1">Terlambat</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $preview['terlambat'] ?? 0 }}</p>
                    </div>

                    {{-- Alpa --}}
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-600 dark:text-red-400 font-medium mb-1">Alpa</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $preview['alpa'] ?? 0 }}</p>
                    </div>

                    {{-- Sakit --}}
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                        <p class="text-sm text-purple-600 dark:text-purple-400 font-medium mb-1">Sakit</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $preview['sakit'] ?? 0 }}</p>
                    </div>

                    {{-- Izin --}}
                    <div class="bg-gray-50 dark:bg-gray-900/20 rounded-lg p-4 border border-gray-200 dark:border-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">Izin</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $preview['izin'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- Additional Stats Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    {{-- Deteksi Mabuk --}}
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-orange-600 dark:text-orange-400 font-medium mb-1">Deteksi Mabuk</p>
                                <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ $preview['deteksi_mabuk'] ?? 0 }}</p>
                            </div>
                            <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-orange-500" />
                        </div>
                    </div>

                    {{-- Date Range --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                        <p class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">Periode</p>
                        <p class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                            {{ $preview['date_range'] ?? '-' }}
                        </p>
                        @if(($preview['total_records'] ?? 0) > 0)
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                Tingkat kehadiran: {{ $preview['percentage']['hadir'] ?? 0 }}%
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Percentage Bar --}}
                @if(($preview['total_records'] ?? 0) > 0)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900/20 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Distribusi Status</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden flex">
                            @php
                                $total = $preview['total_records'];
                                $hadirPct = ($preview['hadir'] / $total) * 100;
                                $izinPct = ($preview['izin'] / $total) * 100;
                                $sakitPct = ($preview['sakit'] / $total) * 100;
                                $alpaPct = ($preview['alpa'] / $total) * 100;
                            @endphp
                            @if($hadirPct > 0)
                                <div class="bg-green-500 h-full flex items-center justify-center text-xs text-white font-medium" 
                                     style="width: {{ $hadirPct }}%"
                                     title="Hadir: {{ round($hadirPct, 1) }}%">
                                    @if($hadirPct > 10){{ round($hadirPct, 0) }}%@endif
                                </div>
                            @endif
                            @if($izinPct > 0)
                                <div class="bg-gray-500 h-full flex items-center justify-center text-xs text-white font-medium" 
                                     style="width: {{ $izinPct }}%"
                                     title="Izin: {{ round($izinPct, 1) }}%">
                                    @if($izinPct > 10){{ round($izinPct, 0) }}%@endif
                                </div>
                            @endif
                            @if($sakitPct > 0)
                                <div class="bg-purple-500 h-full flex items-center justify-center text-xs text-white font-medium" 
                                     style="width: {{ $sakitPct }}%"
                                     title="Sakit: {{ round($sakitPct, 1) }}%">
                                    @if($sakitPct > 10){{ round($sakitPct, 0) }}%@endif
                                </div>
                            @endif
                            @if($alpaPct > 0)
                                <div class="bg-red-500 h-full flex items-center justify-center text-xs text-white font-medium" 
                                     style="width: {{ $alpaPct }}%"
                                     title="Alpa: {{ round($alpaPct, 1) }}%">
                                    @if($alpaPct > 10){{ round($alpaPct, 0) }}%@endif
                                </div>
                            @endif
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-600 dark:text-gray-400">
                            <span>🟢 Hadir {{ $preview['hadir'] }}</span>
                            <span>⚪ Izin {{ $preview['izin'] }}</span>
                            <span>🟣 Sakit {{ $preview['sakit'] }}</span>
                            <span>🔴 Alpa {{ $preview['alpa'] }}</span>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        @endif

        {{-- Quick Report Templates --}}
        <x-filament::section>
            <x-slot name="heading">
                Template Laporan Cepat
            </x-slot>

            <x-slot name="description">
                Klik salah satu template untuk mengisi form secara otomatis
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Daily Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-700 transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->format('Y-m-d') }}'); $set('data.end_date', '{{ today()->format('Y-m-d') }}'); $set('data.report_type', 'attendance')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <x-heroicon-o-calendar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Harian</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan kehadiran untuk hari ini
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        {{ today()->format('d M Y') }}
                    </p>
                </div>

                {{-- Weekly Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg hover:border-green-300 dark:hover:border-green-700 transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->startOfWeek()->format('Y-m-d') }}'); $set('data.end_date', '{{ today()->format('Y-m-d') }}'); $set('data.report_type', 'attendance')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Mingguan</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan kehadiran minggu ini
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        {{ today()->startOfWeek()->format('d M') }} - {{ today()->format('d M Y') }}
                    </p>
                </div>

                {{-- Monthly Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg hover:border-purple-300 dark:hover:border-purple-700 transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->startOfMonth()->format('Y-m-d') }}'); $set('data.end_date', '{{ today()->format('Y-m-d') }}'); $set('data.report_type', 'attendance')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <x-heroicon-o-chart-bar class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Bulanan</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan kehadiran bulan ini
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        {{ today()->format('F Y') }}
                    </p>
                </div>

                {{-- Class Summary --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg hover:border-yellow-300 dark:hover:border-yellow-700 transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->startOfMonth()->format('Y-m-d') }}'); $set('data.end_date', '{{ today()->format('Y-m-d') }}'); $set('data.report_type', 'class_summary')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <x-heroicon-o-academic-cap class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Ringkasan Kelas</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Ringkasan kehadiran per kelas
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        Bulan ini
                    </p>
                </div>

                {{-- Drunk Detection Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg hover:border-red-300 dark:hover:border-red-700 transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->startOfMonth()->format('Y-m-d') }}'); $set('data.end_date', '{{ today()->format('Y-m-d') }}'); $set('data.report_type', 'drunk_detection')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Deteksi Mabuk</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan siswa terdeteksi mabuk
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        Bulan ini
                    </p>
                </div>

                {{-- Late Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-700 transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->startOfMonth()->format('Y-m-d') }}'); $set('data.end_date', '{{ today()->format('Y-m-d') }}'); $set('data.report_type', 'late_report')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                            <x-heroicon-o-clock class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Keterlambatan</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan siswa yang sering terlambat
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        Bulan ini
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Help Section --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-information-circle class="w-5 h-5" />
                    <span>Informasi</span>
                </div>
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <ul class="text-sm space-y-2">
                    <li>Pilih <strong>jenis laporan</strong> yang ingin di-generate</li>
                    <li>Tentukan <strong>periode waktu</strong> (tanggal mulai dan akhir)</li>
                    <li>Filter berdasarkan <strong>kelas</strong> atau <strong>siswa</strong> tertentu (opsional)</li>
                    <li>Pilih <strong>format export</strong>: PDF, Excel, atau CSV</li>
                    <li>Klik tombol <strong>"Generate Laporan"</strong> untuk memproses</li>
                    <li>Atau gunakan <strong>template cepat</strong> untuk periode yang umum digunakan</li>
                </ul>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>