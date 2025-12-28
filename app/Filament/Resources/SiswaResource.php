<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiswaResource\Pages;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Siswa';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Siswa')
                    ->schema([
                        Forms\Components\TextInput::make('kode_siswa')
                            ->label('Kode Siswa / NIS')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30)
                            ->helperText('Nomor Induk Siswa')
                            ->placeholder('2307027'),
                        
                        Forms\Components\TextInput::make('nama_siswa')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(150),
                        
                        Forms\Components\Select::make('id_kelas')
                            ->label('Kelas')
                            ->relationship('kelas', 'id_kelas')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('id_kelas')
                                    ->label('ID Kelas')
                                    ->required(),
                                Forms\Components\Select::make('tingkat')
                                    ->label('Tingkat')
                                    ->options(['X' => 'X', 'XI' => 'XI', 'XII' => 'XII'])
                                    ->required(),
                                Forms\Components\TextInput::make('jurusan')
                                    ->label('Jurusan')
                                    ->required(),
                                Forms\Components\TextInput::make('urutan')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->required(),
                            ]),
                        
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required(),
                        
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->maxDate(now())
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Informasi Wali')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_wali')
                            ->label('Nomor WhatsApp Wali')
                            ->tel()
                            ->maxLength(20)
                            ->helperText('Format: 628xxxxxxxxxx (untuk notifikasi WhatsApp)')
                            ->placeholder('628123456789'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_siswa')
                    ->label('Kode Siswa')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('nama_siswa')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kelas.id_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('JK')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state)
                    ->color(fn (string $state): string => match ($state) {
                        'L' => 'blue',
                        'P' => 'pink',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('umur')
                    ->label('Umur')
                    ->suffix(' tahun')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('tanggal_lahir', $direction === 'asc' ? 'desc' : 'asc');
                    }),
                
                Tables\Columns\TextColumn::make('nomor_wali')
                    ->label('No. Wali')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('absensi_count')
                    ->label('Total Absensi')
                    ->counts('absensi')
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode_siswa')
            ->filters([
                Tables\Filters\SelectFilter::make('id_kelas')
                    ->label('Kelas')
                    ->relationship('kelas', 'id_kelas')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName())
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('view_absensi')
                    ->label('Lihat Absensi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.absensis.index', [
                        'tableFilters' => [
                            'kode_siswa' => ['value' => $record->kode_siswa]
                        ]
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswa::route('/'),
            'create' => Pages\CreateSiswa::route('/create'),
            'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['kelas'])
            ->withCount('absensi');
    }
}