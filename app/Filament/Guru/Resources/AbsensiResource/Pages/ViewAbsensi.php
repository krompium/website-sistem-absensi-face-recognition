<?php

namespace App\Filament\Guru\Resources\AbsensiResource\Pages;

use App\Filament\Guru\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAbsensi extends ViewRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
