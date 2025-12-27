<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('import')
                ->label('Import Siswa')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->action(function () {
                    // TODO: Implement import logic
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
            'all' => Tab::make('Semua Siswa')
                ->badge(fn () => Student::count()),
            
            'active' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('is_active', true)
                )
                ->badge(fn () => Student::where('is_active', true)->count())
                ->badgeColor('success'),
            
            'inactive' => Tab::make('Tidak Aktif')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('is_active', false)
                )
                ->badge(fn () => Student::where('is_active', false)->count())
                ->badgeColor('danger'),
            
            'with_face_data' => Tab::make('Sudah Face Recognition')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereNotNull('face_image')->whereNotNull('face_embeddings')
                )
                ->badge(fn () => Student::whereNotNull('face_image')->whereNotNull('face_embeddings')->count())
                ->badgeColor('info'),
            
            'without_face_data' => Tab::make('Belum Face Recognition')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereNull('face_image')->orWhereNull('face_embeddings')
                )
                ->badge(fn () => Student::whereNull('face_image')->orWhereNull('face_embeddings')->count())
                ->badgeColor('warning'),
        ];
    }
}
