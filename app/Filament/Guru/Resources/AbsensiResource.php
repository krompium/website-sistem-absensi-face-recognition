<?php

namespace App\Filament\Guru\Resources;

use App\Filament\Guru\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Absensi';
    protected static ?string $navigationGroup = 'Monitoring Absensi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Absensi')
                    ->schema([
                        Forms\Components\Select::make('kode_siswa')
                            ->label('Siswa')
                            ->options(function () {
                                // Only show students from guru's classes
                                $user = auth()->user();
                                $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas');
                                
                                return Siswa::whereIn('id_kelas', $kelasIds)
                                    ->orderBy('nama_siswa')
                                    ->get()
                                    ->mapWithKeys(fn ($siswa) => [
                                        $siswa->kode_siswa => "{$siswa->kode_siswa} - {$siswa->nama_siswa} ({$siswa->kelas->id_kelas})"
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $siswa = Siswa::find($state);
                                    if ($siswa) {
                                        $set('id_kelas', $siswa->id_kelas);
                                    }
                                }
                            }),
                        
                        Forms\Components\Select::make('id_kelas')
                            ->label('Kelas')
                            ->relationship('kelas', 'id_kelas')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName())
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required()
                            ->displayFormat('d/m/Y'),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'HADIR' => 'Hadir',
                                'IZIN' => 'Izin',
                                'SAKIT' => 'Sakit',
                                'ALPA' => 'Tanpa Keterangan',
                            ])
                            ->default('HADIR')
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Waktu Absensi')
                    ->schema([
                        Forms\Components\DateTimePicker::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->required(fn (Get $get) => $get('status') === 'HADIR'),
                        
                        Forms\Components\DateTimePicker::make('jam_keluar')
                            ->label('Jam Keluar')
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('status') === 'HADIR'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('siswa.kode_siswa')
                    ->label('Kode Siswa')
                    ->searchable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('siswa.nama_siswa')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kelas.id_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->dateTime('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jam_keluar')
                    ->label('Jam Keluar')
                    ->dateTime('H:i')
                    ->sortable(),
                
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
                
                Tables\Columns\IconColumn::make('ada_indikasi')
                    ->label('Indikasi')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->adaIndikasiMabuk())
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'HADIR' => 'Hadir',
                        'IZIN' => 'Izin',
                        'SAKIT' => 'Sakit',
                        'ALPA' => 'Tanpa Keterangan',
                    ]),
                
                Tables\Filters\SelectFilter::make('id_kelas')
                    ->label('Kelas')
                    ->relationship('kelas', 'id_kelas')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Guru cannot delete absensi
                ]),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsensi::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
            'view' => Pages\ViewAbsensi::route('/{record}'),
        ];
    }
}
