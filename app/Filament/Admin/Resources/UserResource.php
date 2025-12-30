<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manajemen Guru';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Guru')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(150),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(150),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8)
                            ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.')
                            ->hiddenOn('view'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Aktifkan untuk memberikan akses login'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Assign Kelas')
                    ->schema([
                        Forms\Components\CheckboxList::make('kelas')
                            ->label('Kelas yang Diajar')
                            ->relationship('kelasYangDiajar', 'id_kelas')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullName())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3)
                            ->helperText('Pilih kelas yang akan diajar oleh guru ini'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Pending')
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kelasYangDiajar')
                    ->label('Kelas Diajar')
                    ->formatStateUsing(function ($record) {
                        $kelas = $record->kelasYangDiajar;
                        if ($kelas->isEmpty()) {
                            return 'Belum ada kelas';
                        }
                        return $kelas->take(3)->map(fn($k) => $k->getShortName())->join(', ') 
                            . ($kelas->count() > 3 ? ' +' . ($kelas->count() - 3) : '');
                    })
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Pending',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => !$record->is_active)
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['is_active' => true]);
                        
                        Notification::make()
                            ->title('Guru Disetujui')
                            ->body("Akun {$record->name} telah disetujui dan dapat login.")
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['is_active' => false]);
                        
                        Notification::make()
                            ->title('Guru Dinonaktifkan')
                            ->body("Akun {$record->name} telah dinonaktifkan.")
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (!$record->is_active) {
                                    $record->update(['is_active' => true]);
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Guru Disetujui')
                                ->body("{$count} guru telah disetujui.")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Only show guru users
        return parent::getEloquentQuery()->where('role', 'guru');
    }

    public static function getNavigationBadge(): ?string
    {
        // Show count of pending approvals
        $pending = User::where('role', 'guru')->where('is_active', false)->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
