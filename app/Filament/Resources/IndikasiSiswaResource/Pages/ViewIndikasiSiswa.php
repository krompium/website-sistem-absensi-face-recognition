<?php
// =====================================================
// File: app/Filament/Resources/IndikasiSiswaResource/Pages/ViewIndikasiSiswa.php
namespace App\Filament\Resources\IndikasiSiswaResource\Pages;

use App\Filament\Resources\IndikasiSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIndikasiSiswa extends ViewRecord
{
    protected static string $resource = IndikasiSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_absensi')
                ->label('Lihat Absensi')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.absensis.edit', [
                    'record' => $this->record->id_absensi
                ])),
            
            Actions\Action::make('view_siswa')
                ->label('Lihat Siswa')
                ->icon('heroicon-o-user')
                ->color('primary')
                ->url(fn () => route('filament.admin.resources.siswas.edit', [
                    'record' => $this->record->absensi->kode_siswa
                ])),
            
            Actions\DeleteAction::make(),
        ];
    }
}