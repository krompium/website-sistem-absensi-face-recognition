<?php
// File: app/Filament/Resources/KelasResource/Pages/ListKelas.php
namespace App\Filament\Resources\KelasResource\Pages;

use App\Filament\Resources\KelasResource;
use App\Models\Kelas;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Kelas')
                ->badge(Kelas::count()),
            
            'x' => Tab::make('Kelas X')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tingkat', 'X'))
                ->badge(Kelas::where('tingkat', 'X')->count())
                ->badgeColor('info'),
            
            'xi' => Tab::make('Kelas XI')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tingkat', 'XI'))
                ->badge(Kelas::where('tingkat', 'XI')->count())
                ->badgeColor('success'),
            
            'xii' => Tab::make('Kelas XII')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tingkat', 'XII'))
                ->badge(Kelas::where('tingkat', 'XII')->count())
                ->badgeColor('warning'),
        ];
    }
}