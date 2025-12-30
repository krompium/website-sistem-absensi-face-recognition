<?php
// File: app/Filament/Resources/SiswaResource/Pages/ListSiswa.php
namespace App\Filament\Resources\SiswaResource\Pages;

use App\Filament\Admin\Resources\SiswaResource;
use App\Models\Siswa;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSiswa extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('import')
                ->label('Import Siswa')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->action(function () {
                    \Filament\Notifications\Notification::make()
                        ->title('Fitur import akan segera tersedia')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Siswa')
                ->badge(Siswa::count()),
            
            'laki_laki' => Tab::make('Laki-laki')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenis_kelamin', 'L'))
                ->badge(Siswa::where('jenis_kelamin', 'L')->count())
                ->badgeColor('blue'),
            
            'perempuan' => Tab::make('Perempuan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenis_kelamin', 'P'))
                ->badge(Siswa::where('jenis_kelamin', 'P')->count())
                ->badgeColor('pink'),
        ];
    }
}