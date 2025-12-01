<?php
// app/Filament/Resources/NotificationResource/Pages/ListNotifications.php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('retry_failed')
                ->label('Coba Ulang Semua Gagal')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $failedNotifications = \App\Models\Notification::where('status', 'failed')
                        ->where('retry_count', '<', 3)
                        ->get();
                    
                    // TODO: Implement retry logic
                    
                    \Filament\Notifications\Notification::make()
                        ->title(count($failedNotifications) . ' notifikasi akan dicoba ulang')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => \App\Models\Notification::whereDate('created_at', today())->count()),
            
            'pending' => Tab::make('Menunggu')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'pending')
                )
                ->badge(fn () => \App\Models\Notification::where('status', 'pending')->count())
                ->badgeColor('warning'),
            
            'sent' => Tab::make('Terkirim')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereIn('status', ['sent', 'delivered', 'read'])
                )
                ->badge(fn () => \App\Models\Notification::whereIn('status', ['sent', 'delivered', 'read'])
                    ->whereDate('created_at', today())
                    ->count())
                ->badgeColor('success'),
            
            'failed' => Tab::make('Gagal')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'failed')
                )
                ->badge(fn () => \App\Models\Notification::where('status', 'failed')->count())
                ->badgeColor('danger'),
            
            'drunk_detection' => Tab::make('Deteksi Mabuk')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('type', 'drunk_detection')
                )
                ->badge(fn () => \App\Models\Notification::where('type', 'drunk_detection')
                    ->whereDate('created_at', today())
                    ->count())
                ->badgeColor('danger'),
            
            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereDate('created_at', today())
                )
                ->badge(fn () => \App\Models\Notification::whereDate('created_at', today())->count()),
        ];
    }
}


// =====================================================

// app/Filament/Resources/NotificationResource/Pages/ViewNotification.php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('resend')
                ->label('Kirim Ulang')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn () => $this->record->canRetry())
                ->requiresConfirmation()
                ->action(function () {
                    // TODO: Implement resend logic
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Notifikasi akan dikirim ulang')
                        ->success()
                        ->send();
                }),
        ];
    }
}