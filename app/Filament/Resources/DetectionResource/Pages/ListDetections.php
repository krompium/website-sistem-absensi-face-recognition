<?php

namespace App\Filament\Resources\DetectionResource\Pages;

use App\Filament\Resources\DetectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetections extends ListRecords
{
    protected static string $resource = DetectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
