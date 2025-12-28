<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAttendancesWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Absensi Terbaru Hari Ini')
            ->query(
                Absensi::query()
                    ->whereDate('tanggal', today()) // Fix: date → tanggal
                    ->with(['siswa', 'kelas']) // Fix: student → siswa, class → kelas
                    ->latest('jam_masuk') // Fix: check_in_time → jam_masuk
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->dateTime('H:i') // Fix: time → dateTime
                    ->sortable(),

                Tables\Columns\TextColumn::make('siswa.kode_siswa') // Fix: student.nis → siswa.kode_siswa
                    ->label('Kode Siswa')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('siswa.nama_siswa') // Fix: student.name → siswa.nama_siswa
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kelas.id_kelas') // Fix: student.class.name → kelas.id_kelas
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'HADIR' => 'success',
                        'IZIN' => 'info',
                        'SAKIT' => 'warning',
                        'ALPA' => 'danger',
                        default => 'gray',
                    }),

                // ========== FIX: Indikasi mabuk dari relasi ==========
                Tables\Columns\IconColumn::make('ada_indikasi')
                    ->label('Indikasi Mabuk')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->adaIndikasiMabuk()) // Method dari model
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('indikasi_count')
                    ->label('Jml Deteksi')
                    ->counts('indikasi')
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('jam_keluar')
                    ->label('Jam Keluar')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.absensis.edit', ['record' => $record->id_absensi]))
                    ->openUrlInNewTab(false),
            ]);
    }
}