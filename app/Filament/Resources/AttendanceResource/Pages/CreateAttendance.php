<?php

// =====================================================
// File: app/Filament/Resources/AttendanceResource/Pages/CreateAttendance.php
// =====================================================

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data absensi berhasil ditambahkan';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default drunk_status if not provided
        if (!isset($data['drunk_status'])) {
            $data['drunk_status'] = 'sober';
        }
        
        // Set default values for boolean fields
        if (!isset($data['red_eyes'])) {
            $data['red_eyes'] = false;
        }
        
        if (!isset($data['unstable_posture'])) {
            $data['unstable_posture'] = false;
        }
        
        if (!isset($data['parent_notified'])) {
            $data['parent_notified'] = false;
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Auto-send notification if drunk detected
        if ($this->record->needsNotification()) {
            // TODO: Implement auto-send notification logic
            // WhatsAppService::sendDrunkDetectionNotification($this->record);
            
            \Filament\Notifications\Notification::make()
                ->title('Deteksi Mabuk Ditemukan')
                ->body('Notifikasi akan dikirim ke orang tua siswa')
                ->warning()
                ->send();
        }
    }
}
