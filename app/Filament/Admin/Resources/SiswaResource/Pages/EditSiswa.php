<?php

// =====================================================
// File: app/Filament/Resources/SiswaResource/Pages/EditSiswa.php
namespace App\Filament\Admin\Resources\SiswaResource\Pages;

use App\Filament\Admin\Resources\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSiswa extends EditRecord
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            Actions\Action::make('view_absensi')
                ->label('Lihat Riwayat Absensi')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.absensis.index', [
                    'tableFilters' => [
                        'kode_siswa' => ['value' => $this->record->kode_siswa]
                    ]
                ])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data siswa berhasil diperbarui';
    }
}