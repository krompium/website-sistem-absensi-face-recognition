<?php

namespace App\Filament\Guru\Resources;

use App\Filament\Guru\Resources\IndikasiSiswaResource\Pages;
use App\Models\IndikasiSiswa;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class IndikasiSiswaResource extends Resource
{
    protected static ?string $model = IndikasiSiswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Indikasi Siswa';
    protected static ?string $navigationGroup = 'Monitoring Absensi';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('absensi.tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('absensi.siswa.kode_siswa')
                    ->label('Kode Siswa')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('absensi.siswa.nama_siswa')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('absensi.kelas.id_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('attendance_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => $state === 'in' ? 'Masuk' : 'Keluar')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'in' ? 'success' : 'warning'),
                
                Tables\Columns\TextColumn::make('final_decision')
                    ->label('Keputusan')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'SOBER' => 'Normal',
                        'DRUNK INDICATION' => 'Terindikasi Mabuk',
                        'INCONCLUSIVE' => 'Tidak Jelas',
                        default => 'Unknown',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SOBER' => 'success',
                        'DRUNK INDICATION' => 'danger',
                        'INCONCLUSIVE' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('average_prob_sober')
                    ->label('Avg Prob')
                    ->formatStateUsing(fn ($state) => $state ? round($state * 100, 1) . '%' : '-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('final_decision')
                    ->label('Keputusan')
                    ->options([
                        'SOBER' => 'Normal',
                        'DRUNK INDICATION' => 'Terindikasi Mabuk',
                        'INCONCLUSIVE' => 'Tidak Jelas',
                    ]),
                
                Tables\Filters\SelectFilter::make('attendance_type')
                    ->label('Tipe Absensi')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Siswa')
                    ->schema([
                        Infolists\Components\TextEntry::make('absensi.siswa.kode_siswa')
                            ->label('Kode Siswa'),
                        Infolists\Components\TextEntry::make('absensi.siswa.nama_siswa')
                            ->label('Nama Siswa'),
                        Infolists\Components\TextEntry::make('absensi.kelas.id_kelas')
                            ->label('Kelas')
                            ->formatStateUsing(fn ($record) => $record->absensi->kelas?->getFullName()),
                        Infolists\Components\TextEntry::make('absensi.tanggal')
                            ->label('Tanggal Absensi')
                            ->date('d F Y'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Hasil Deteksi')
                    ->schema([
                        Infolists\Components\TextEntry::make('attendance_type')
                            ->label('Tipe Absensi')
                            ->formatStateUsing(fn (string $state): string => $state === 'in' ? 'Masuk' : 'Keluar')
                            ->badge(),
                        Infolists\Components\TextEntry::make('final_decision')
                            ->label('Keputusan')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'SOBER' => 'Normal/Sadar',
                                'DRUNK INDICATION' => 'Terindikasi Mabuk',
                                'INCONCLUSIVE' => 'Tidak Dapat Disimpulkan',
                                default => 'Unknown',
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'SOBER' => 'success',
                                'DRUNK INDICATION' => 'danger',
                                'INCONCLUSIVE' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('frames_used')
                            ->label('Frame Digunakan'),
                        Infolists\Components\TextEntry::make('average_prob_sober')
                            ->label('Average Probability')
                            ->formatStateUsing(fn ($state) => $state ? round($state * 100, 2) . '%' : '-'),
                        Infolists\Components\TextEntry::make('median_prob_sober')
                            ->label('Median Probability')
                            ->formatStateUsing(fn ($state) => $state ? round($state * 100, 2) . '%' : '-'),
                        Infolists\Components\TextEntry::make('session_id')
                            ->label('Session ID')
                            ->copyable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIndikasiSiswa::route('/'),
            'view' => Pages\ViewIndikasiSiswa::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Guru cannot create indikasi
    }

    public static function getNavigationBadge(): ?string
    {
        // Use the model query which respects global scopes
        $count = static::getModel()::where('final_decision', 'DRUNK INDICATION')
            ->whereHas('absensi', function ($query) {
                $query->whereDate('tanggal', '>=', now()->subDays(7));
            })
            ->count();
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
