<?php
// =====================================================

// app/Filament/Resources/NotificationResource/Pages/EditNotification.php

// namespace App\Filament\Resources\NotificationResource\Pages;

// use App\Filament\Resources\NotificationResource;
// use Filament\Actions;
// use Filament\Resources\Pages\EditRecord;

// class EditNotification extends EditRecord
// {
//     protected static string $resource = NotificationResource::class;

//     protected function getHeaderActions(): array
//     {
//         return [
//             Actions\DeleteAction::make(),
            
//             Actions\Action::make('resend')
//                 ->label('Kirim Ulang')
//                 ->icon('heroicon-o-paper-airplane')
//                 ->color('warning')
//                 ->visible(fn () => $this->record->canRetry())
//                 ->requiresConfirmation()
//                 ->action(function () {
//                     // TODO: Implement resend logic
                    
//                     \Filament\Notifications\Notification::make()
//                         ->title('Notifikasi akan dikirim ulang')
//                         ->success()
//                         ->send();
//                 }),
            
//             Actions\Action::make('view_student')
//                 ->label('Lihat Siswa')
//                 ->icon('heroicon-o-user')
//                 ->url(fn () => route('filament.admin.resources.students.edit', [
//                     'record' => $this->record->student_id
//                 ])),
            
//             Actions\Action::make('view_detection')
//                 ->label('Lihat Deteksi')
//                 ->icon('heroicon-o-exclamation-triangle')
//                 ->visible(fn () => $this->record->detection_id !== null)
//                 ->url(fn () => route('filament.admin.resources.detections.edit', [
//                     'record' => $this->record->detection_id
//                 ])),
//         ];
//     }

//     protected function getRedirectUrl(): string
//     {
//         return $this->getResource()::getUrl('index');
//     }
// }

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
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
            
            Actions\Action::make('view_student')
                ->label('Lihat Siswa')
                ->icon('heroicon-o-user')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.students.edit', [
                    'record' => $this->record->student_id
                ])),
            
            // ========== PERUBAHAN: HAPUS ACTION VIEW_DETECTION ==========
            // Karena tabel detections sudah dihapus, sekarang redirect ke attendance
            Actions\Action::make('view_attendance')
                ->label('Lihat Absensi')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn () => $this->record->attendance_id !== null)
                ->url(fn () => route('filament.admin.resources.attendances.edit', [
                    'record' => $this->record->attendance_id
                ])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}