<?php

// =====================================================
// File: app/Filament/Resources/AbsensiResource/Pages/CreateAbsensi.php
namespace App\Filament\Admin\Resources\AbsensiResource\Pages;

use App\Filament\Admin\Resources\AbsensiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAbsensi extends CreateRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data absensi berhasil ditambahkan';
    }
}
