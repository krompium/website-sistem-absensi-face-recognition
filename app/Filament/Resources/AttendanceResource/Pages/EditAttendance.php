<?php

// =====================================================
// File: app/Filament/Resources/AttendanceResource/Pages/EditAttendance.php
// =====================================================

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            Actions\Action::make('view_student')
                ->label('Lihat Siswa')
                ->icon('heroicon-o-user')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.students.edit', [
                    'record' => $this->record->student_id
                ])),
            
            Actions\Action::make('send_notification')
                ->label('Kirim Notifikasi ke Orang Tua')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn () => $this->record->needsNotification())
                ->requiresConfirmation()
                ->modalHeading('Kirim Notifikasi')
                ->modalDescription(fn () => 
                    "Kirim notifikasi deteksi mabuk untuk {$this->record->student->name} ke {$this->record->student->parent_name}"
                )
                ->action(function () {
                    // TODO: Implement notification logic
                    // WhatsAppService::sendDrunkDetectionNotification($this->record);
                    
                    $this->record->markParentNotified();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Notifikasi berhasil dikirim')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data absensi berhasil diperbarui';
    }
}