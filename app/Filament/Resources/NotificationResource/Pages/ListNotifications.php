<?php
// app/Filament/Resources/NotificationResource/Pages/ListNotifications.php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\WhatsappNotification;
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
                    $failedNotifications = WhatsappNotification::where('status', 'failed')
                        ->where('retry_count', '<', 3)
                        ->get();
                    
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
                ->badge(fn () => WhatsappNotification::whereDate('created_at', today())->count()),
            
            'pending' => Tab::make('Menunggu')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'pending')
                )
                ->badge(fn () => WhatsappNotification::where('status', 'pending')->count())
                ->badgeColor('warning'),
            
            'sent' => Tab::make('Terkirim')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereIn('status', ['sent', 'delivered', 'read'])
                )
                ->badge(fn () => WhatsappNotification::whereIn('status', ['sent', 'delivered', 'read'])
                    ->whereDate('created_at', today())
                    ->count())
                ->badgeColor('success'),
            
            'failed' => Tab::make('Gagal')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'failed')
                )
                ->badge(fn () => WhatsappNotification::where('status', 'failed')->count())
                ->badgeColor('danger'),
            
            'drunk_detection' => Tab::make('Deteksi Mabuk')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('type', 'drunk_detection')
                )
                ->badge(fn () => WhatsappNotification::where('type', 'drunk_detection')
                    ->whereDate('created_at', today())
                    ->count())
                ->badgeColor('danger'),
            
            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereDate('created_at', today())
                )
                ->badge(fn () => WhatsappNotification::whereDate('created_at', today())->count()),
        ];
    }
}