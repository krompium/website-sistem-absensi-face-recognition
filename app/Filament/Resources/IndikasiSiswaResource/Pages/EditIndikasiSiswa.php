<?php

namespace App\Filament\Resources\IndikasiSiswaResource\Pages;

use App\Filament\Resources\IndikasiSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIndikasiSiswa extends EditRecord
{
    protected static string $resource = IndikasiSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
