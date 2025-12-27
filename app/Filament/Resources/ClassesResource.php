<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassesResource\Pages;
use App\Models\Classes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClassesResource extends Resource
{
    protected static ?string $model = Classes::class;
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
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kelas')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Contoh: XII RPL 2'),
                        
                        Forms\Components\Select::make('grade')
                            ->label('Tingkat')
                            ->options([
                                '10' => 'Kelas X',
                                '11' => 'Kelas XI',
                                '12' => 'Kelas XII',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('major')
                            ->label('Jurusan')
                            ->maxLength(255)
                            ->helperText('Contoh: RPL, TKJ, MM')
                            ->nullable(),
                        
                        // ========== FIELD BARU: SEQUENCE ==========
                        Forms\Components\TextInput::make('sequence')
                            ->label('Urutan Kelas')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Contoh: XII RPL 2 → urutan = 2')
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('homeroom_teacher')
                            ->label('Wali Kelas')
                            ->maxLength(255)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('capacity')
                            ->label('Kapasitas Siswa')
                            ->numeric()
                            ->default(30)
                            ->minValue(1)
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('grade')
                    ->label('Tingkat')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => "Kelas {$state}")
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('major')
                    ->label('Jurusan')
                    ->badge()
                    ->color('success')
                    ->default('-'),
                
                Tables\Columns\TextColumn::make('sequence')
                    ->label('Urutan')
                    ->sortable()
                    ->default('-'),
                
                Tables\Columns\TextColumn::make('homeroom_teacher')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // ========== COLUMN BARU: TEACHER RELATION ==========
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru Pengampu')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student_count')
                    ->label('Jumlah Siswa')
                    ->getStateUsing(fn ($record) => $record->getStudentCount())
                    ->badge()
                    ->color(fn ($record) => $record->isFull() ? 'danger' : 'success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('grade')
            ->filters([
                Tables\Filters\SelectFilter::make('grade')
                    ->label('Tingkat')
                    ->options([
                        '10' => 'Kelas X',
                        '11' => 'Kelas XI',
                        '12' => 'Kelas XII',
                    ]),
                
                Tables\Filters\SelectFilter::make('major')
                    ->label('Jurusan')
                    ->options(function () {
                        return Classes::query()
                            ->whereNotNull('major')
                            ->distinct()
                            ->pluck('major', 'major')
                            ->toArray();
                    })
                    ->searchable(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                
                Tables\Filters\TernaryFilter::make('is_full')
                    ->label('Kapasitas')
                    ->placeholder('Semua')
                    ->trueLabel('Penuh')
                    ->falseLabel('Tersedia')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('students', function ($q) {
                            $q->havingRaw('COUNT(*) >= classes.capacity');
                        }),
                        false: fn (Builder $query) => $query->whereDoesntHave('students')
                            ->orWhereHas('students', function ($q) {
                                $q->havingRaw('COUNT(*) < classes.capacity');
                            }),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('view_students')
                    ->label('Lihat Siswa')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.students.index', [
                        'tableFilters' => [
                            'class_id' => ['value' => $record->id]
                        ]
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClasses::route('/create'),
            'edit' => Pages\EditClasses::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('students')
            ->with(['teacher']);
    }
}