<?php

// =====================================================
// File: app/Filament/Resources/AttendanceResource/Pages/ListAttendances.php
// =====================================================

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // TODO: Implement export logic
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
            'all' => Tab::make('Semua')
                ->badge(fn () => Attendance::whereDate('date', today())->count()),
            
            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereDate('date', today())
                )
                ->badge(fn () => Attendance::whereDate('date', today())->count())
                ->badgeColor('info'),
            
            'present' => Tab::make('Hadir')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'present')->whereDate('date', today())
                )
                ->badge(fn () => Attendance::where('status', 'present')->whereDate('date', today())->count())
                ->badgeColor('success'),
            
            'late' => Tab::make('Terlambat')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'late')->whereDate('date', today())
                )
                ->badge(fn () => Attendance::where('status', 'late')->whereDate('date', today())->count())
                ->badgeColor('warning'),
            
            'absent' => Tab::make('Tidak Hadir')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'absent')->whereDate('date', today())
                )
                ->badge(fn () => Attendance::where('status', 'absent')->whereDate('date', today())->count())
                ->badgeColor('danger'),
            
            'drunk_detected' => Tab::make('Terdeteksi Mabuk')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereIn('drunk_status', ['suspected', 'drunk'])
                        ->whereDate('date', today())
                )
                ->badge(fn () => Attendance::whereIn('drunk_status', ['suspected', 'drunk'])
                    ->whereDate('date', today())
                    ->count())
                ->badgeColor('danger'),
        ];
    }
}
