<?php

namespace App\Filament\Guru\Resources\IndikasiSiswaResource\Pages;

use App\Filament\Guru\Resources\IndikasiSiswaResource;
use Filament\Resources\Pages\ListRecords;

class ListIndikasiSiswa extends ListRecords
{
    protected static string $resource = IndikasiSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for guru
        ];
    }
}
