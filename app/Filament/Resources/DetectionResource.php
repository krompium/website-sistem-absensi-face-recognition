<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetectionResource\Pages;
use App\Models\Detection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DetectionResource extends Resource
{
    protected static ?string $model = Detection::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Deteksi Mabuk';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Deteksi')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Select::make('attendance_id')
                            ->label('Absensi')
                            ->relationship('attendance', 'id')
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Select::make('drunk_status')
                            ->label('Status Deteksi')
                            ->options([
                                'sober' => 'Normal',
                                'suspected' => 'Terindikasi',
                                'drunk' => 'Terdeteksi Mabuk',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('drunk_confidence')
                            ->label('Confidence Score (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Detail Deteksi')
                    ->schema([
                        Forms\Components\Toggle::make('red_eyes')
                            ->label('Mata Merah'),
                        
                        Forms\Components\Toggle::make('unstable_posture')
                            ->label('Postur Tidak Stabil'),
                        
                        Forms\Components\Select::make('severity')
                            ->label('Tingkat Keparahan')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                            ])
                            ->default('low')
                            ->required(),
                        
                        Forms\Components\Textarea::make('ai_analysis')
                            ->label('Analisis AI')
                            ->rows(3),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Notifikasi')
                    ->schema([
                        Forms\Components\Toggle::make('notification_sent')
                            ->label('Notifikasi Terkirim')
                            ->disabled(),
                        
                        Forms\Components\DateTimePicker::make('notification_sent_at')
                            ->label('Waktu Notifikasi Terkirim')
                            ->disabled(),
                    ])
                    ->columns(2),
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
                
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('drunk_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sober' => 'success',
                        'suspected' => 'warning',
                        'drunk' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sober' => 'Normal',
                        'suspected' => 'Terindikasi',
                        'drunk' => 'Mabuk',
                    }),
                
                Tables\Columns\TextColumn::make('drunk_confidence')
                    ->label('Confidence')
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('severity')
                    ->label('Keparahan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'info',
                        'medium' => 'warning',
                        'high' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                    }),
                
                Tables\Columns\IconColumn::make('red_eyes')
                    ->label('Mata Merah')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('notification_sent')
                    ->label('Notif')
                    ->boolean()
                    ->color('success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('drunk_status')
                    ->label('Status')
                    ->options([
                        'sober' => 'Normal',
                        'suspected' => 'Terindikasi',
                        'drunk' => 'Mabuk',
                    ]),
                
                Tables\Filters\SelectFilter::make('severity')
                    ->label('Keparahan')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                    ]),
                
                Filter::make('needs_attention')
                    ->label('Perlu Perhatian')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereIn('drunk_status', ['suspected', 'drunk'])
                    ),
                
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereDate('created_at', today())
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('send_notification')
                    ->label('Kirim Notifikasi')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->visible(fn ($record) => $record->needsNotification())
                    ->action(function ($record) {
                        // TODO: Implement notification logic
                    }),
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
            'index' => Pages\ListDetections::route('/'),
            'create' => Pages\CreateDetection::route('/create'),
            'edit' => Pages\EditDetection::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('drunk_status', ['suspected', 'drunk'])
            ->whereDate('created_at', today())
            ->count();
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}