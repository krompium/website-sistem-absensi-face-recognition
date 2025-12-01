{{-- resources/views/filament/pages/realtime-monitor.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Data
            </x-slot>
            
            {{ $this->form }}
        </x-filament::section>

        {{-- Statistics Cards --}}
        @php
            $stats = $this->getStats();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Hadir</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                        <x-heroicon-o-users class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tepat Waktu</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['present'] }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Terlambat</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['late'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                        <x-heroicon-o-clock class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak Hadir</p>
                        <p class="text-3xl font-bold text-red-600">{{ $stats['absent'] }}</p>
                    </div>
                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                        <x-heroicon-o-x-circle class="w-8 h-8 text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Rate Progress --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat Kehadiran</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $stats['percentage'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                <div class="h-4 rounded-full transition-all duration-500 {{ $stats['percentage'] >= 80 ? 'bg-green-600' : ($stats['percentage'] >= 60 ? 'bg-yellow-600' : 'bg-red-600') }}" 
                     style="width: {{ $stats['percentage'] }}%">
                </div>
            </div>
        </div>

        {{-- Live Attendance List --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span>Data Absensi Real-time</span>
                    <span class="flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suhu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Confidence</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                        @forelse($this->getAttendanceData() as $attendance)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $attendance->check_in_time->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attendance->student->face_image)
                                        <img src="{{ Storage::url($attendance->student->face_image) }}" 
                                             class="h-10 w-10 rounded-full object-cover"
                                             alt="{{ $attendance->student->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <x-heroicon-o-user class="w-6 h-6 text-gray-600" />
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $attendance->student->nis }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $attendance->student->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $attendance->student->class->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $attendance->status === 'present' ? 'Hadir' : 'Terlambat' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm 
                                    {{ $attendance->temperature >= 37.5 ? 'text-red-600 font-bold' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $attendance->temperature ?? '-' }}°C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded 
                                        {{ $attendance->check_in_confidence >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ number_format($attendance->check_in_confidence, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-4 text-gray-400" />
                                    <p>Belum ada data absensi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>

    {{-- Auto-refresh every 10 seconds --}}
    <script>
        setInterval(function() {
            window.location.reload();
        }, 10000);
    </script>
</x-filament-panels::page>