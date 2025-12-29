<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
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
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Absensi')
                    ->schema([
                        Forms\Components\Select::make('kode_siswa')
                            ->label('Siswa')
                            ->relationship('siswa', 'nama_siswa', function ($query) {
                                return $query->orderBy('nama_siswa');
                            })
                            ->searchable(['kode_siswa', 'nama_siswa'])
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $siswa = \App\Models\Siswa::find($state);
                                    if ($siswa) {
                                        $set('id_kelas', $siswa->id_kelas);
                                    }
                                }
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->kode_siswa} - {$record->nama_siswa}"
                            ),
                        
                        Forms\Components\Select::make('id_kelas')
                            ->label('Kelas')
                            ->relationship('kelas', 'id_kelas')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName())
                            ->searchable()
                            ->preload()
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
                    ->label('Indikasi Mabuk')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->adaIndikasiMabuk())
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
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'HADIR' => 'Hadir',
                        'IZIN' => 'Izin',
                        'SAKIT' => 'Sakit',
                        'ALPA' => 'Tanpa Keterangan',
                    ]),
                
                Tables\Filters\SelectFilter::make('id_kelas')
                    ->label('Kelas')
                    ->relationship('kelas', 'id_kelas')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName())
                    ->searchable()
                    ->preload(),
                
                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
                
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('tanggal', today())),
                
                Filter::make('ada_indikasi_mabuk')
                    ->label('Ada Indikasi Mabuk')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('indikasi', function ($q) {
                            $q->where('final_decision', 'DRUNK INDICATION');
                        })
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('view_indikasi')
                    ->label('Lihat Deteksi')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->visible(fn ($record) => $record->adaIndikasiMabuk())
                    ->url(fn ($record) => route('filament.admin.resources.indikasi-siswas.index', [
                        'tableFilters' => [
                            'id_absensi' => ['value' => $record->id_absensi]
                        ]
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
            'index' => Pages\ListAbsensi::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('tanggal', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['siswa', 'kelas'])
            ->withCount('indikasi');
    }
}