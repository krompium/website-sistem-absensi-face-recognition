<?php
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
                    // Akses $this->record yang merupakan instance WhatsappNotification
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Notifikasi akan dikirim ulang')
                        ->success()
                        ->send();
                }),
        ];
    }
}