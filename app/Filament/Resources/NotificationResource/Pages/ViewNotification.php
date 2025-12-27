<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('resend')
                ->label('Kirim Ulang')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn () => $this->record->canRetry())
                ->requiresConfirmation()
                ->modalHeading('Kirim Ulang Notifikasi')
                ->modalDescription(fn () => 
                    "Kirim ulang notifikasi ke {$this->record->recipient_name} ({$this->record->recipient_phone})"
                )
                ->action(function () {
                    // TODO: Implement resend logic
                    // WhatsAppService::resendNotification($this->record);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Notifikasi akan dikirim ulang')
                        ->success()
                        ->send();
                }),
            
            Actions\Action::make('mark_as_sent')
                ->label('Tandai Terkirim')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->isPending() || $this->record->isFailed())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->markAsSent();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Status berhasil diubah menjadi terkirim')
                        ->success()
                        ->send();
                }),
            
            Actions\Action::make('view_student')
                ->label('Lihat Siswa')
                ->icon('heroicon-o-user')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.students.edit', [
                    'record' => $this->record->student_id
                ])),
            
            Actions\Action::make('view_attendance')
                ->label('Lihat Absensi')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn () => $this->record->attendance_id !== null)
                ->url(fn () => route('filament.admin.resources.attendances.edit', [
                    'record' => $this->record->attendance_id
                ])),
            
            Actions\DeleteAction::make(),
        ];
    }
}