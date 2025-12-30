<?php

// =====================================================
// File: app/Filament/Resources/AbsensiResource/Pages/EditAbsensi.php
namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Admin\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbsensi extends EditRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            Actions\Action::make('view_siswa')
                ->label('Lihat Siswa')
                ->icon('heroicon-o-user')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.siswas.edit', [
                    'record' => $this->record->kode_siswa
                ])),
            
            Actions\Action::make('view_indikasi')
                ->label('Lihat Deteksi Mabuk')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->visible(fn () => $this->record->adaIndikasiMabuk())
                ->url(fn () => route('filament.admin.resources.indikasi-siswas.index', [
                    'tableFilters' => [
                        'id_absensi' => ['value' => $this->record->id_absensi]
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
        return 'Data absensi berhasil diperbarui';
    }
}