<?php
// =====================================================

// app/Filament/Resources/NotificationResource/Pages/CreateNotification.php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default status
        if (empty($data['status'])) {
            $data['status'] = 'pending';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Auto-send notification if status is pending
        if ($this->record->status === 'pending') {
            // TODO: Implement auto-send logic
            
            \Filament\Notifications\Notification::make()
                ->title('Notifikasi Dibuat')
                ->body('Notifikasi akan dikirim segera')
                ->success()
                ->send();
        }
    }
}