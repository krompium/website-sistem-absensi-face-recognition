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
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                        <p class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">Total Records</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $preview['total_records'] }}</p>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">Hadir</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $preview['present'] }}</p>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
                        <p class="text-sm text-yellow-600 dark:text-yellow-400 font-medium mb-1">Terlambat</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $preview['late'] }}</p>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-600 dark:text-red-400 font-medium mb-1">Tidak Hadir</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $preview['absent'] }}</p>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                        <p class="text-sm text-purple-600 dark:text-purple-400 font-medium mb-1">Sakit</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $preview['sick'] }}</p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/20 rounded-lg p-4 border border-gray-200 dark:border-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-1">Izin</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $preview['permission'] }}</p>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-blue-900 dark:text-blue-100">
                        <span class="font-medium">Periode:</span> {{ $preview['date_range'] }}
                    </p>
                </div>
            </x-filament::section>
        @endif

        {{-- Quick Report Templates --}}
        <x-filament::section>
            <x-slot name="heading">
                Template Laporan Cepat
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Daily Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today() }}'); $set('data.end_date', '{{ today() }}'); $set('data.report_type', 'attendance')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <x-heroicon-o-calendar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Harian</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan kehadiran untuk hari ini
                    </p>
                </div>

                {{-- Weekly Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->startOfWeek() }}'); $set('data.end_date', '{{ today() }}'); $set('data.report_type', 'attendance')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Mingguan</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan kehadiran minggu ini
                    </p>
                </div>

                {{-- Monthly Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition cursor-pointer"
                     wire:click="$set('data.start_date', '{{ today()->startOfMonth() }}'); $set('data.end_date', '{{ today() }}'); $set('data.report_type', 'attendance')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <x-heroicon-o-chart-bar class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Bulanan</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan kehadiran bulan ini
                    </p>
                </div>

                {{-- Class Summary --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition cursor-pointer"
                     wire:click="$set('data.report_type', 'class_summary')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <x-heroicon-o-academic-cap class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Ringkasan Kelas</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Ringkasan kehadiran per kelas
                    </p>
                </div>

                {{-- Drunk Detection Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition cursor-pointer"
                     wire:click="$set('data.report_type', 'drunk_detection')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Deteksi Mabuk</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan siswa terdeteksi mabuk
                    </p>
                </div>

                {{-- Late Report --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition cursor-pointer"
                     wire:click="$set('data.report_type', 'late_report')">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                            <x-heroicon-o-clock class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Keterlambatan</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Laporan siswa yang sering terlambat
                    </p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>