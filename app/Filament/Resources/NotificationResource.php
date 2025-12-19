<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\WhatsappNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = WhatsappNotification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Notifikasi';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penerima')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $student = \App\Models\Student::find($state);
                                    if ($student) {
                                        $set('recipient_name', $student->parent_name);
                                        $set('recipient_phone', $student->parent_phone);
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('Nama Penerima')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('No. WhatsApp')
                            ->required()
                            ->tel()
                            ->maxLength(20)
                            ->helperText('Format: 628xxxxxxxxxx'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Konten Notifikasi')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe Notifikasi')
                            ->options([
                                'attendance' => 'Kehadiran',
                                'drunk_detection' => 'Deteksi Mabuk',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'general' => 'Umum',
                            ])
                            ->required()
                            ->default('general'),
                        
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('message')
                            ->label('Pesan')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status Pengiriman')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'sent' => 'Terkirim',
                                'failed' => 'Gagal',
                                'delivered' => 'Tersampaikan',
                                'read' => 'Dibaca',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('sent_at')
                            ->label('Waktu Terkirim')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('retry_count')
                            ->label('Jumlah Percobaan')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('whatsapp_response')
                            ->label('Response WhatsApp API')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'drunk_detection' => 'danger',
                        'late' => 'warning',
                        'absent' => 'warning',
                        'attendance' => 'success',
                        'general' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'attendance' => 'Kehadiran',
                        'drunk_detection' => 'Deteksi Mabuk',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'general' => 'Umum',
                        default => ucfirst($state),
                    }),
                
                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('recipient_phone')
                    ->label('No. WhatsApp')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(30)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'delivered' => 'success',
                        'read' => 'info',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'sent' => 'Terkirim',
                        'failed' => 'Gagal',
                        'delivered' => 'Tersampaikan',
                        'read' => 'Dibaca',
                        default => ucfirst($state),
                    }),
                
                Tables\Columns\TextColumn::make('retry_count')
                    ->label('Percobaan')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'attendance' => 'Kehadiran',
                        'drunk_detection' => 'Deteksi Mabuk',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'general' => 'Umum',
                    ]),
                
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'sent' => 'Terkirim',
                        'failed' => 'Gagal',
                        'delivered' => 'Tersampaikan',
                        'read' => 'Dibaca',
                    ]),
                
                Filter::make('failed')
                    ->label('Gagal Terkirim')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'failed')),
                
                Filter::make('pending')
                    ->label('Menunggu')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending')),
                
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereDate('created_at', today())
                    ),
                
                Filter::make('drunk_detection')
                    ->label('Deteksi Mabuk')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('type', 'drunk_detection')
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('resend')
                    ->label('Kirim Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->canRetry())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // TODO: Implement resend notification logic
                        // $this->sendWhatsAppNotification($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Notifikasi akan dikirim ulang')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('mark_as_sent')
                    ->label('Tandai Terkirim')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->markAsSent();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Status berhasil diubah')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_as_sent')
                        ->label('Tandai Terkirim')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->markAsSent();
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Status berhasil diubah')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
            'view' => Pages\ViewNotification::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'failed')
            ->whereDate('created_at', today())
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
            ->with(['student', 'student.class', 'detection']);
    }
}