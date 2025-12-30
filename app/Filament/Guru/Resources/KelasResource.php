<?php

namespace App\Filament\Guru\Resources;

use App\Filament\Guru\Resources\KelasResource\Pages;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Kelas Saya';
    protected static ?string $navigationGroup = 'Data Kelas';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_kelas')
                    ->label('ID Kelas')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->badge()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('siswa_count')
                    ->label('Jumlah Siswa')
                    ->counts('siswa')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tingkat')
                    ->options([
                        'X' => 'X',
                        'XI' => 'XI',
                        'XII' => 'XII',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('tingkat');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Kelas')
                    ->schema([
                        Infolists\Components\TextEntry::make('id_kelas')
                            ->label('ID Kelas'),
                        Infolists\Components\TextEntry::make('tingkat')
                            ->label('Tingkat')
                            ->badge(),
                        Infolists\Components\TextEntry::make('jurusan')
                            ->label('Jurusan'),
                        Infolists\Components\TextEntry::make('urutan')
                            ->label('Urutan'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statistik')
                    ->schema([
                        Infolists\Components\TextEntry::make('siswa_count')
                            ->label('Jumlah Siswa')
                            ->state(fn ($record) => $record->siswa()->count()),
                        Infolists\Components\TextEntry::make('hadir_hari_ini')
                            ->label('Hadir Hari Ini')
                            ->state(fn ($record) => $record->absensi()
                                ->whereDate('tanggal', today())
                                ->where('status', 'HADIR')
                                ->count()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelas::route('/'),
            'view' => Pages\ViewKelas::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        
        // Filter to only show classes assigned to this guru
        return parent::getEloquentQuery()
            ->whereHas('guru', function ($query) use ($user) {
                $query->where('id_user', $user->id_user);
            });
    }

    public static function canCreate(): bool
    {
        return false; // Guru cannot create classes
    }
}
