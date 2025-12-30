<?php

namespace App\Filament\Guru\Resources\KelasResource\Pages;

use App\Filament\Guru\Resources\KelasResource;
use Filament\Resources\Pages\ListRecords;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for guru
        ];
    }
}
