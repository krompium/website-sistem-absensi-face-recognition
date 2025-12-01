<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
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
                        Forms\Components\Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'sick' => 'Sakit',
                                'permission' => 'Izin',
                            ])
                            ->default('present')
                            ->required(),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Waktu Check In/Out')
                    ->schema([
                        Forms\Components\TimePicker::make('check_in_time')
                            ->label('Jam Masuk'),
                        
                        Forms\Components\TimePicker::make('check_out_time')
                            ->label('Jam Keluar'),
                        
                        Forms\Components\TextInput::make('temperature')
                            ->label('Suhu Tubuh (°C)')
                            ->numeric()
                            ->suffix('°C'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Data Face Recognition')
                    ->schema([
                        Forms\Components\FileUpload::make('check_in_photo')
                            ->label('Foto Check In')
                            ->image()
                            ->directory('attendance'),
                        
                        Forms\Components\TextInput::make('check_in_confidence')
                            ->label('Confidence Check In (%)')
                            ->numeric()
                            ->suffix('%'),
                        
                        Forms\Components\FileUpload::make('check_out_photo')
                            ->label('Foto Check Out')
                            ->image()
                            ->directory('attendance'),
                        
                        Forms\Components\TextInput::make('check_out_confidence')
                            ->label('Confidence Check Out (%)')
                            ->numeric()
                            ->suffix('%'),
                    ])
                    ->columns(2),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Jam Keluar')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        'sick' => 'info',
                        'permission' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'sick' => 'Sakit',
                        'permission' => 'Izin',
                    }),
                
                Tables\Columns\IconColumn::make('has_drunk_detection')
                    ->label('Deteksi Mabuk')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasDrunkDetection())
                    ->color('danger'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'sick' => 'Sakit',
                        'permission' => 'Izin',
                    ]),
                
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('date', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}