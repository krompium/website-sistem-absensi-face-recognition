<?php
namespace App\Filament\Resources;

use App\Filament\Resources\IndikasiSiswaResource\Pages;
use App\Models\IndikasiSiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry; // ✅ TAMBAHKAN INI
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IndikasiSiswaResource extends Resource
{
    protected static ?string $model            = IndikasiSiswa::class;
    protected static ?string $navigationIcon   = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel  = 'Deteksi Mabuk';
    protected static ?string $navigationGroup  = 'Monitoring';
    protected static ?int $navigationSort      = 4;
    protected static ?string $pluralModelLabel = 'Deteksi Mabuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Deteksi')
                    ->schema([
                        Forms\Components\Select::make('id_absensi')
                            ->label('Absensi')
                            ->relationship('absensi', 'id_absensi')
                            ->getOptionLabelFromRecordUsing(fn($record) =>
                                "#{$record->id_absensi} - {$record->siswa->nama_siswa} ({$record->tanggal->format('d/m/Y')})"
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('session_id')
                            ->label('Session ID')
                            ->maxLength(100)
                            ->disabled(),

                        Forms\Components\Select::make('attendance_type')
                            ->label('Tipe Absensi')
                            ->options([
                                'in'  => 'Masuk',
                                'out' => 'Keluar',
                            ])
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('final_decision')
                            ->label('Keputusan Akhir')
                            ->options([
                                'SOBER'            => 'Normal/Sadar',
                                'DRUNK INDICATION' => 'Terindikasi Mabuk',
                                'INCONCLUSIVE'     => 'Tidak Dapat Disimpulkan',
                            ])
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Analisis AI')
                    ->schema([
                        Forms\Components\TextInput::make('frames_used')
                            ->label('Jumlah Frame')
                            ->numeric()
                            ->disabled()
                            ->suffix('frames'),

                        Forms\Components\TextInput::make('average_prob_sober')
                            ->label('Average Probability (Sober)')
                            ->numeric()
                            ->disabled()
                            ->helperText('Nilai 0-1 (semakin tinggi = semakin yakin SOBER)'),

                        Forms\Components\TextInput::make('median_prob_sober')
                            ->label('Median Probability (Sober)')
                            ->numeric()
                            ->disabled()
                            ->helperText('Nilai 0-1 (semakin tinggi = semakin yakin SOBER)'),

                        Forms\Components\TextInput::make('face_image')
                            ->label('Path Foto Wajah')
                            ->maxLength(255)
                            ->disabled(),

                        Forms\Components\TextInput::make('frames_dir')
                            ->label('Directory Frames')
                            ->maxLength(255)
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Deteksi')
                    ->schema([
                        TextEntry::make('absensi. id_absensi')
                            ->label('Absensi')
                            ->formatStateUsing(fn($record) =>
                                "#{$record->absensi->id_absensi} - {$record->absensi->siswa->nama_siswa} ({$record->absensi->tanggal->format('d/m/Y')})"
                            )
                            ->url(fn($record) => route('filament.admin.resources.absensis.edit', [
                                'record' => $record->id_absensi,
                            ]))
                            ->color('primary'),

                        TextEntry::make('session_id')
                            ->label('Session ID')
                            ->copyable()
                            ->icon('heroicon-o-clipboard'),

                        TextEntry::make('attendance_type')
                            ->label('Tipe Absensi')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'in'    => 'Masuk',
                                'out'   => 'Keluar',
                                default => ucfirst($state),
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'in'    => 'success',
                                'out'   => 'primary',
                                default => 'gray',
                            }),

                        TextEntry::make('final_decision')
                            ->label('Keputusan Akhir')
                            ->badge()
                            ->size('lg')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'SOBER'            => 'Normal/Sadar',
                                'DRUNK INDICATION' => 'Terindikasi Mabuk',
                                'INCONCLUSIVE'     => 'Tidak Dapat Disimpulkan',
                                default            => $state,
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'SOBER'            => 'success',
                                'DRUNK INDICATION' => 'danger',
                                'INCONCLUSIVE'     => 'warning',
                                default            => 'gray',
                            }),
                    ])
                    ->columns(2),

                Section::make('Data Analisis AI')
                    ->schema([
                        TextEntry::make('frames_used')
                            ->label('Jumlah Frame')
                            ->suffix(' frames')
                            ->icon('heroicon-o-film'),

                        TextEntry::make('average_prob_sober')
                            ->label('Average Probability (Sober)')
                            ->numeric(decimalPlaces: 3)
                            ->helperText('Nilai 0-1 (semakin tinggi = semakin yakin SOBER)')
                            ->color(fn($state) => $state >= 0.7 ? 'success' : ($state <= 0.3 ? 'danger' : 'warning')),

                        TextEntry::make('median_prob_sober')
                            ->label('Median Probability (Sober)')
                            ->numeric(decimalPlaces: 3)
                            ->helperText('Nilai 0-1 (semakin tinggi = semakin yakin SOBER)')
                            ->color(fn($state) => $state >= 0.7 ? 'success' : ($state <= 0.3 ? 'danger' : 'warning')),
                    ])
                    ->columns(3),

                Section::make('Media & Frames')
                    ->schema([
                        ImageEntry::make('face_image')
                            ->label('Foto Wajah')
                            ->getStateUsing(function ($record) {
                                if ($record->face_image) {
                                    return route('secure.image.face', $record->session_id);
                                }
                                return null;
                            })
                            ->height(200)
                            ->defaultImageUrl(url('/images/no-face.png')),

                        TextEntry::make('face_image')
                            ->label('Path Foto Wajah')
                            ->copyable()
                            ->icon('heroicon-o-folder'),

                        TextEntry::make('frames_dir')
                            ->label('Frames Info')
                            ->formatStateUsing(fn($state) => $state ? 'Tersedia' : 'Tidak ada frames')
                            ->suffix(fn($record) => $record->frames_used ? " ({$record->frames_used} frames)" : '')
                            ->icon('heroicon-o-photo')
                            ->color('primary'),
                    ])
                    ->columns(2),

                // Frames GAllery
                Section::make('Frames Gallery')
                    ->description(function ($record) {
                        $frameCount = $record->frames_used ?? 0;
                        return $frameCount > 0
                            ? "Tersedia ({$frameCount} frames)"
                            : 'Tidak ada frames';
                    })
                    ->schema([
                        \Filament\Infolists\Components\Actions::make([
                            \Filament\Infolists\Components\Actions\Action::make('open_carousel')
                                ->label(fn($record) => "🎬 Lihat {$record->frames_used} Frames dalam Carousel")
                                ->icon('heroicon-o-play-circle')
                                ->color('primary')
                                ->size('lg')
                                ->button()
                                ->modalHeading(fn($record) => "Frames Gallery - Session {$record->session_id}")
                                ->modalDescription(fn($record) => "Total {$record->frames_used} frames dari sesi deteksi")
                                ->modalWidth('7xl')
                                ->modalContent(fn($record) => view('filament.modals.frames-carousel-modal', [
                                    'record' => $record,
                                ]))
                                ->modalCancelAction(false) // ✅ Hilangkan tombol cancel
                                ->visible(fn($record) => $record->frames_used > 0),
                        ]),
                    ])
                    ->collapsible()
                    ->visible(fn($record) => ($record->frames_used ?? 0) > 0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Deteksi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('absensi.siswa. kode_siswa')
                    ->label('Kode Siswa')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('absensi.siswa.nama_siswa')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('absensi.kelas.id_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('attendance_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in'    => 'Masuk',
                        'out'   => 'Keluar',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'in'    => 'success',
                        'out'   => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('final_decision')
                    ->label('Hasil Deteksi')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'SOBER'            => 'Normal',
                        'DRUNK INDICATION' => 'Mabuk',
                        'INCONCLUSIVE'     => 'Ragu',
                        default            => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'SOBER'            => 'success',
                        'DRUNK INDICATION' => 'danger',
                        'INCONCLUSIVE'     => 'warning',
                        default            => 'gray',
                    }),

                Tables\Columns\TextColumn::make('average_prob_sober')
                    ->label('Avg Prob')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('median_prob_sober')
                    ->label('Median Prob')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('session_id')
                    ->label('Session ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // ✅ FACE IMAGE COLUMN
                Tables\Columns\ImageColumn::make('face_preview')
                    ->label('Face')
                    ->getStateUsing(function ($record) {
                        if ($record->face_image) {
                            return route('secure.image.face', $record->session_id);
                        }
                        return null;
                    })
                    ->size(60)
                    ->circular(),

                // ✅ FRAMES COLUMN (DIPERBAIKI - Tidak duplikat)
                Tables\Columns\TextColumn::make('frames_info')
                    ->label('Frames')
                    ->getStateUsing(fn($record) => $record->frames_used)
                    ->suffix(' frames')
                    ->action(
                        Tables\Actions\Action::make('view_frames')
                            ->label('View Frames')
                            ->icon('heroicon-o-photo')
                            ->modalHeading(fn($record) => "Frames - {$record->session_id}")
                            ->modalWidth('7xl')
                            ->modalContent(fn($record) => view('filament.components.frames-gallery', [
                                'record' => $record,
                            ]))
                            ->modalFooterActions([])
                            ->visible(fn($record) => $record->frames_dir !== null && $record->frames_used > 0)
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('final_decision')
                    ->label('Hasil Deteksi')
                    ->options([
                        'SOBER'            => 'Normal/Sadar',
                        'DRUNK INDICATION' => 'Terindikasi Mabuk',
                        'INCONCLUSIVE'     => 'Tidak Dapat Disimpulkan',
                    ]),

                Tables\Filters\SelectFilter::make('attendance_type')
                    ->label('Tipe Absensi')
                    ->options([
                        'in'  => 'Masuk',
                        'out' => 'Keluar',
                    ]),

                Filter::make('drunk_only')
                    ->label('Hanya Indikasi Mabuk')
                    ->query(fn(Builder $query): Builder =>
                        $query->where('final_decision', 'DRUNK INDICATION')
                    ),

                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn(Builder $query): Builder =>
                        $query->whereHas('absensi', function ($q) {
                            $q->whereDate('tanggal', today());
                        })
                    ),

                Filter::make('high_confidence')
                    ->label('High Confidence (>70%)')
                    ->query(fn(Builder $query): Builder =>
                        $query->where(function ($q) {
                            $q->where('average_prob_sober', '>=', 0.7)
                                ->orWhere('average_prob_sober', '<=', 0.3);
                        })
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('view_absensi')
                    ->label('Lihat Absensi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->url(fn($record) => route('filament.admin.resources.absensis.edit', [
                        'record' => $record->id_absensi,
                    ])),

                Tables\Actions\Action::make('view_siswa')
                    ->label('Lihat Siswa')
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->url(fn($record) => route('filament. admin.resources.siswas. edit', [
                        'record' => $record->absensi->kode_siswa,
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIndikasiSiswa::route('/'),
            'view'  => Pages\ViewIndikasiSiswa::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('final_decision', 'DRUNK INDICATION')
            ->whereHas('absensi', function ($q) {
                $q->whereDate('tanggal', today());
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['absensi', 'absensi.siswa', 'absensi.kelas']);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
