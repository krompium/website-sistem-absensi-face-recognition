<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IndikasiSiswaResource\Pages;
use App\Models\IndikasiSiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class IndikasiSiswaResource extends Resource
{
    protected static ?string $model = IndikasiSiswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Deteksi Mabuk';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 4;
    protected static ?string $pluralModelLabel = 'Deteksi Mabuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Deteksi')
                    ->schema([
                        Forms\Components\Select::make('id_absensi')
                            ->label('Absensi')
                            ->relationship('absensi', 'id_absensi')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "#{$record->id_absensi} - {$record->siswa->nama_siswa} ({$record->tanggal->format('d/m/Y')})"
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('session_id')
                            ->label('Session ID')
                            ->maxLength(100)
                            ->disabled(),
                        
                        Forms\Components\Select::make('attendance_type')
                            ->label('Tipe Absensi')
                            ->options([
                                'in' => 'Masuk',
                                'out' => 'Keluar',
                            ])
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Select::make('final_decision')
                            ->label('Keputusan Akhir')
                            ->options([
                                'SOBER' => 'Normal/Sadar',
                                'DRUNK INDICATION' => 'Terindikasi Mabuk',
                                'INCONCLUSIVE' => 'Tidak Dapat Disimpulkan',
                            ])
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Data Analisis AI')
                    ->schema([
                        Forms\Components\TextInput::make('frames_used')
                            ->label('Jumlah Frame')
                            ->numeric()
                            ->disabled()
                            ->suffix('frames'),
                        
                        Forms\Components\TextInput::make('average_prob_sober')
                            ->label('Average Probability (Sober)')
                            ->numeric()
                            ->disabled()
                            ->helperText('Nilai 0-1 (semakin tinggi = semakin yakin SOBER)'),
                        
                        Forms\Components\TextInput::make('median_prob_sober')
                            ->label('Median Probability (Sober)')
                            ->numeric()
                            ->disabled()
                            ->helperText('Nilai 0-1 (semakin tinggi = semakin yakin SOBER)'),
                        
                        Forms\Components\TextInput::make('face_image')
                            ->label('Path Foto Wajah')
                            ->maxLength(255)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('frames_dir')
                            ->label('Directory Frames')
                            ->maxLength(255)
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Deteksi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('absensi.siswa.kode_siswa')
                    ->label('Kode Siswa')
                    ->searchable()
                    ->copyable(),
                
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
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'primary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('final_decision')
                    ->label('Hasil Deteksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'SOBER' => 'Normal',
                        'DRUNK INDICATION' => 'Mabuk',
                        'INCONCLUSIVE' => 'Ragu',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'SOBER' => 'success',
                        'DRUNK INDICATION' => 'danger',
                        'INCONCLUSIVE' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('average_prob_sober')
                    ->label('Avg Prob')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('median_prob_sober')
                    ->label('Median Prob')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('frames_used')
                    ->label('Frames')
                    ->suffix(' frames')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('session_id')
                    ->label('Session ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('final_decision')
                    ->label('Hasil Deteksi')
                    ->options([
                        'SOBER' => 'Normal/Sadar',
                        'DRUNK INDICATION' => 'Terindikasi Mabuk',
                        'INCONCLUSIVE' => 'Tidak Dapat Disimpulkan',
                    ]),
                
                Tables\Filters\SelectFilter::make('attendance_type')
                    ->label('Tipe Absensi')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                    ]),
                
                Filter::make('drunk_only')
                    ->label('Hanya Indikasi Mabuk')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('final_decision', 'DRUNK INDICATION')
                    ),
                
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('absensi', function ($q) {
                            $q->whereDate('tanggal', today());
                        })
                    ),
                
                Filter::make('high_confidence')
                    ->label('High Confidence (>70%)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where(function($q) {
                            $q->where('average_prob_sober', '>=', 0.7)
                              ->orWhere('average_prob_sober', '<=', 0.3);
                        })
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('view_absensi')
                    ->label('Lihat Absensi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.absensis.edit', [
                        'record' => $record->id_absensi
                    ])),
                
                Tables\Actions\Action::make('view_siswa')
                    ->label('Lihat Siswa')
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->url(fn ($record) => route('filament.admin.resources.siswas.edit', [
                        'record' => $record->absensi->kode_siswa
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIndikasiSiswa::route('/'),
            'view' => Pages\ViewIndikasiSiswa::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('final_decision', 'DRUNK INDICATION')
            ->whereHas('absensi', function ($q) {
                $q->whereDate('tanggal', today());
            })
            ->count();
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['absensi', 'absensi.siswa', 'absensi.kelas']);
    }

    public static function canCreate(): bool
    {
        return false; // Tidak bisa create manual, hanya dari AI
    }
}