<?php

// File: app/Filament/Resources/IndikasiSiswaResource/Pages/ListIndikasiSiswa.php
namespace App\Filament\Resources\IndikasiSiswaResource\Pages;

use App\Filament\Admin\Resources\IndikasiSiswaResource;
use App\Models\IndikasiSiswa;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListIndikasiSiswa extends ListRecords
{
    protected static string $resource = IndikasiSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(fn () => $this->redirect(static::getResource()::getUrl('index'))),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge(IndikasiSiswa::count()),
            
            'hari_ini' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('absensi', function ($q) {
                        $q->whereDate('tanggal', today());
                    })
                )
                ->badge(IndikasiSiswa::whereHas('absensi', function ($q) {
                    $q->whereDate('tanggal', today());
                })->count())
                ->badgeColor('info'),
            
            'drunk_indication' => Tab::make('Terindikasi Mabuk')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('final_decision', 'DRUNK INDICATION')
                )
                ->badge(IndikasiSiswa::where('final_decision', 'DRUNK INDICATION')->count())
                ->badgeColor('danger'),
            
            'sober' => Tab::make('Normal')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('final_decision', 'SOBER')
                )
                ->badge(IndikasiSiswa::where('final_decision', 'SOBER')->count())
                ->badgeColor('success'),
            
            'inconclusive' => Tab::make('Tidak Jelas')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('final_decision', 'INCONCLUSIVE')
                )
                ->badge(IndikasiSiswa::where('final_decision', 'INCONCLUSIVE')->count())
                ->badgeColor('warning'),
        ];
    }
}