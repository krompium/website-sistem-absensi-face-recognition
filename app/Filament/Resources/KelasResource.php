<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Kelas';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kelas')
                    ->schema([
                        Forms\Components\TextInput::make('id_kelas')
                            ->label('ID Kelas')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30)
                            ->helperText('Contoh: XIIRPL, XTKJ1, XIMM2')
                            ->placeholder('XIIRPL'),
                        
                        Forms\Components\Select::make('tingkat')
                            ->label('Tingkat')
                            ->options([
                                'X' => 'Kelas X',
                                'XI' => 'Kelas XI',
                                'XII' => 'Kelas XII',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('jurusan')
                            ->label('Jurusan')
                            ->required()
                            ->maxLength(50)
                            ->helperText('Contoh: Rekayasa Perangkat Lunak, Teknik Komputer Jaringan')
                            ->placeholder('Rekayasa Perangkat Lunak'),
                        
                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Kelas')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Contoh: XII RPL 2 → urutan = 2')
                            ->default(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_kelas')
                    ->label('ID Kelas')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('ID Kelas disalin'),
                
                Tables\Columns\TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => "Kelas {$state}")
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('siswa_count')
                    ->label('Jumlah Siswa')
                    ->counts('siswa')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('guru_count')
                    ->label('Jumlah Guru')
                    ->counts('guru')
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tingkat')
            ->filters([
                Tables\Filters\SelectFilter::make('tingkat')
                    ->label('Tingkat')
                    ->options([
                        'X' => 'Kelas X',
                        'XI' => 'Kelas XI',
                        'XII' => 'Kelas XII',
                    ]),
                
                Tables\Filters\SelectFilter::make('jurusan')
                    ->label('Jurusan')
                    ->options(function () {
                        return Kelas::query()
                            ->distinct()
                            ->pluck('jurusan', 'jurusan')
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('view_siswa')
                    ->label('Lihat Siswa')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.siswas.index', [
                        'tableFilters' => [
                            'id_kelas' => ['value' => $record->id_kelas]
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
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['siswa', 'guru']);
    }
}