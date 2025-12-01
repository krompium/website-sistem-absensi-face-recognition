<?php
// app/Filament/Widgets/DrunkDetectionAlertWidget.php

namespace App\Filament\Widgets;

use App\Models\Detection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DrunkDetectionAlertWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚠️ Deteksi Mabuk - Perlu Perhatian')
            ->query(
                Detection::query()
                    ->whereIn('drunk_status', ['suspected', 'drunk'])
                    ->with(['student', 'student.class', 'attendance'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('student.face_image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->size(40),

                Tables\Columns\TextColumn::make('student.nis')
                    ->label('NIS')
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('drunk_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'drunk' => 'danger',
                        'suspected' => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'drunk' => 'Mabuk',
                        'suspected' => 'Terindikasi',
                        default => 'Normal',
                    }),

                Tables\Columns\TextColumn::make('drunk_confidence')
                    ->label('Confidence')
                    ->suffix('%')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('severity')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'high' => 'Tinggi',
                        'medium' => 'Sedang',
                        'low' => 'Rendah',
                        default => ucfirst($state),
                    }),

                Tables\Columns\IconColumn::make('red_eyes')
                    ->label('Mata Merah')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('notification_sent')
                    ->label('Notif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.detections.edit', ['record' => $record->id]))
                    ->color('info'),

                Tables\Actions\Action::make('send_notification')
                    ->label('Kirim Notif')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn ($record) => !$record->notification_sent)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // TODO: Implement notification sending
                        \Filament\Notifications\Notification::make()
                            ->title('Notifikasi akan dikirim')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Tidak ada deteksi mabuk')
            ->emptyStateDescription('Semua siswa dalam kondisi normal')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}