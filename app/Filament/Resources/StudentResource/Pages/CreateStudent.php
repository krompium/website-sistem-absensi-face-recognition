<?php

// =====================================================
// File: app/Filament/Resources/StudentResource/Pages/CreateStudent.php
// =====================================================

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Siswa berhasil ditambahkan';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values if needed
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }
        
        return $data;
    }
}