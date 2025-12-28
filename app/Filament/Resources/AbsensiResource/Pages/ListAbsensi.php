<?php

// File: app/Filament/Resources/AbsensiResource/Pages/ListAbsensi.php
namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use App\Models\Absensi;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAbsensi extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    \Filament\Notifications\Notification::make()
                        ->title('Fitur export akan segera tersedia')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge(Absensi::whereDate('tanggal', today())->count()),
            
            'hari_ini' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('tanggal', today()))
                ->badge(Absensi::whereDate('tanggal', today())->count())
                ->badgeColor('info'),
            
            'hadir' => Tab::make('Hadir')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'HADIR')->whereDate('tanggal', today())
                )
                ->badge(Absensi::where('status', 'HADIR')->whereDate('tanggal', today())->count())
                ->badgeColor('success'),
            
            'izin' => Tab::make('Izin')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'IZIN')->whereDate('tanggal', today())
                )
                ->badge(Absensi::where('status', 'IZIN')->whereDate('tanggal', today())->count())
                ->badgeColor('info'),
            
            'sakit' => Tab::make('Sakit')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'SAKIT')->whereDate('tanggal', today())
                )
                ->badge(Absensi::where('status', 'SAKIT')->whereDate('tanggal', today())->count())
                ->badgeColor('warning'),
            
            'alpa' => Tab::make('Alpa')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'ALPA')->whereDate('tanggal', today())
                )
                ->badge(Absensi::where('status', 'ALPA')->whereDate('tanggal', today())->count())
                ->badgeColor('danger'),
            
            'indikasi_mabuk' => Tab::make('Indikasi Mabuk')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('indikasi', function ($q) {
                        $q->where('final_decision', 'DRUNK INDICATION');
                    })->whereDate('tanggal', today())
                )
                ->badge(Absensi::whereHas('indikasi', function ($q) {
                    $q->where('final_decision', 'DRUNK INDICATION');
                })->whereDate('tanggal', today())->count())
                ->badgeColor('danger'),
        ];
    }
}