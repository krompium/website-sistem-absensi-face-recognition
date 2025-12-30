<?php

namespace App\Filament\Guru\Resources;

use App\Filament\Guru\Resources\SiswaResource\Pages;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Siswa';
    protected static ?string $navigationGroup = 'Data Kelas';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_siswa')
                    ->label('Kode Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nama_siswa')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kelas.id_kelas')
                    ->label('Kelas')
                    ->formatStateUsing(fn ($record) => $record->kelas?->getFullName())
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('JK')
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'Laki-laki' : 'Perempuan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'L' => 'info',
                        'P' => 'warning',
                    }),
                
                Tables\Columns\TextColumn::make('umur')
                    ->label('Umur')
                    ->suffix(' tahun'),
                
                Tables\Columns\TextColumn::make('nomor_wali')
                    ->label('No. Wali')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_kelas')
                    ->label('Kelas')
                    ->relationship('kelas', 'id_kelas')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName()),
                
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('nama_siswa');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Siswa')
                    ->schema([
                        Infolists\Components\TextEntry::make('kode_siswa')
                            ->label('Kode Siswa'),
                        Infolists\Components\TextEntry::make('nama_siswa')
                            ->label('Nama Lengkap'),
                        Infolists\Components\TextEntry::make('kelas.id_kelas')
                            ->label('Kelas')
                            ->formatStateUsing(fn ($record) => $record->kelas?->getFullName()),
                        Infolists\Components\TextEntry::make('jenis_kelamin_label')
                            ->label('Jenis Kelamin'),
                        Infolists\Components\TextEntry::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('umur')
                            ->label('Umur')
                            ->suffix(' tahun'),
                        Infolists\Components\TextEntry::make('nomor_wali')
                            ->label('Nomor WhatsApp Wali'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statistik Absensi')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_absensi')
                            ->label('Total Absensi')
                            ->state(fn ($record) => $record->absensi()->count()),
                        Infolists\Components\TextEntry::make('persentase_kehadiran')
                            ->label('Persentase Kehadiran (30 hari)')
                            ->state(fn ($record) => $record->getPersentaseKehadiran(30))
                            ->suffix('%'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswa::route('/'),
            'view' => Pages\ViewSiswa::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Guru cannot create students
    }
}
