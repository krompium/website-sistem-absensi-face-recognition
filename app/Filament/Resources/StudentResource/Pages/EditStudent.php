<?php

// =====================================================
// File: app/Filament/Resources/StudentResource/Pages/EditStudent.php
// =====================================================

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
            
            Actions\Action::make('view_attendance')
                ->label('Lihat Riwayat Absensi')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.attendances.index', [
                    'tableFilters' => [
                        'student_id' => ['value' => $this->record->id]
                    ]
                ])),
            
            Actions\Action::make('view_notifications')
                ->label('Lihat Notifikasi')
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->url(fn () => route('filament.admin.resources.notifications.index', [
                    'tableFilters' => [
                        'student_id' => ['value' => $this->record->id]
                    ]
                ])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data siswa berhasil diperbarui';
    }
}