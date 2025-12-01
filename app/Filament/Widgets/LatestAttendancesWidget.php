<?php
// app/Filament/Widgets/LatestAttendancesWidget.php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAttendancesWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Absensi Terbaru Hari Ini')
            ->query(
                Attendance::query()
                    ->whereDate('date', today())
                    ->with(['student', 'student.class'])
                    ->latest('check_in_time')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('student.face_image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->size(40),

                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.nis')
                    ->label('NIS')
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('temperature')
                    ->label('Suhu')
                    ->suffix('°C')
                    ->color(fn ($state) => $state >= 37.5 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('check_in_confidence')
                    ->label('Confidence')
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : 'warning'),

                Tables\Columns\IconColumn::make('has_drunk_detection')
                    ->label('Deteksi')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasDrunkDetection())
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.attendances.edit', ['record' => $record->id]))
                    ->openUrlInNewTab(false),
            ]);
    }
}