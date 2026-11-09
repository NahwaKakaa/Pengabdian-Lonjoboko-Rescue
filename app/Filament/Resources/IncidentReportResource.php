<?php

namespace App\Filament\Resources;

use App\Enums\ReportKind;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Filament\Resources\IncidentReportResource\Pages;
use App\Models\DisasterEvent;
use App\Models\IncidentReport;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

/**
 * Laporan kejadian dari warga.
 * Alur: Baru masuk -> kader Karang Taruna cek ke lokasi -> admin mengesahkan.
 */
class IncidentReportResource extends Resource
{
    protected static ?string $model = IncidentReport::class;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Laporan & Kejadian';

    protected static ?string $modelLabel = 'laporan warga';

    protected static ?string $pluralModelLabel = 'Laporan Warga';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = IncidentReport::whereIn('status', [ReportStatus::Baru, ReportStatus::Dicek])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return IncidentReport::where('status', ReportStatus::Baru)->exists() ? 'danger' : 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Belum selesai ditindaklanjuti';
    }

    private static function isAdmin(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function form(Form $form): Form
    {
        $info = fn (string $label, callable $value) => Placeholder::make(str($label)->slug('_')->toString())
            ->label($label)
            ->content(fn (?IncidentReport $record) => $record ? ($value($record) ?: '-') : '-');

        return $form->schema([
            Section::make('Isi laporan warga')
                ->columns(3)
                ->schema([
                    $info('Nomor laporan', fn ($r) => $r->code),
                    $info('Jenis kejadian', fn ($r) => $r->kind?->getLabel()),
                    $info('Masuk', fn ($r) => $r->created_at?->timezone('Asia/Makassar')->translatedFormat('j F Y, H.i').' WITA'),
                    $info('Dusun', fn ($r) => $r->hamlet),
                    $info('Patokan lokasi', fn ($r) => $r->landmark),
                    Placeholder::make('map_link')
                        ->label('Titik GPS')
                        ->content(fn (?IncidentReport $record) => $record?->hasValidLocation()
                            ? new HtmlString('<a href="https://www.google.com/maps/search/?api=1&query='.$record->lat.','.$record->lng.'" target="_blank" rel="noopener" class="font-semibold text-primary-600 underline dark:text-primary-400">Buka di Google Maps</a>')
                            : 'Tidak dikirim'),
                    $info('Keterangan', fn ($r) => $r->description)->columnSpanFull(),
                    $info('Pelapor', fn ($r) => trim(($r->reporter_name ?: 'Tanpa nama').($r->reporter_phone ? ' · '.$r->reporter_phone : ''))),
                    Placeholder::make('photo_preview')
                        ->label('Foto')
                        ->columnSpan(2)
                        ->content(fn (?IncidentReport $record) => $record?->photo
                            ? new HtmlString('<a href="'.e(Storage::disk('public')->url($record->photo)).'" target="_blank" rel="noopener"><img src="'.e(Storage::disk('public')->url($record->photo)).'" alt="Foto laporan" style="max-height:260px;border-radius:12px"></a>')
                            : 'Tidak ada foto'),
                ]),

            Section::make('Tindak lanjut')
                ->description(fn () => self::isAdmin()
                    ? 'Kader Karang Taruna mengecek ke lokasi, lalu admin mengesahkan. Laporan yang disahkan bisa dijadikan riwayat kejadian.'
                    : 'Cek ke lokasi bila aman, tulis apa yang Anda lihat, lalu pilih "Sudah dicek kader".')
                ->schema([
                    ToggleButtons::make('status')
                        ->label('Status')
                        ->inline()
                        ->required()
                        ->options(fn () => collect(ReportStatus::cases())
                            ->filter(fn (ReportStatus $s) => self::isAdmin() || in_array($s, [ReportStatus::Baru, ReportStatus::Dicek], true))
                            ->mapWithKeys(fn (ReportStatus $s) => [$s->value => $s->getLabel()])
                            ->all())
                        ->colors(collect(ReportStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->getColor()])->all()),
                    Textarea::make('check_note')
                        ->label('Hasil pengecekan di lapangan')
                        ->rows(3)
                        ->placeholder('Contoh: retakan sepanjang 5 m di kebun belakang rumah warga, 3 rumah perlu waspada'),
                    Placeholder::make('checked_info')
                        ->label('Dicek oleh')
                        ->visible(fn (?IncidentReport $record) => (bool) $record?->checked_by)
                        ->content(fn (?IncidentReport $record) => $record?->checker?->name.' · '.$record?->checked_at?->timezone('Asia/Makassar')->translatedFormat('j M Y, H.i')),
                    Textarea::make('admin_note')
                        ->label('Catatan admin desa')
                        ->rows(2)
                        ->visible(fn () => self::isAdmin()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Masuk')->since()->sortable()
                    ->description(fn (IncidentReport $r) => $r->code),
                TextColumn::make('kind')->label('Jenis')->badge()
                    ->formatStateUsing(fn (ReportKind $state) => $state->shortLabel()),
                TextColumn::make('description')->label('Keterangan')->limit(70)->wrap()->searchable(),
                TextColumn::make('hamlet')->label('Dusun')->placeholder('-'),
                IconColumn::make('photo')->label('Foto')->boolean()->getStateUsing(fn (IncidentReport $r) => (bool) $r->photo),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Status')->options(ReportStatus::class),
                SelectFilter::make('kind')->label('Jenis')->options(ReportKind::class),
            ])
            ->actions([
                Action::make('toEvent')
                    ->label('Jadikan riwayat')
                    ->icon('heroicon-m-clock')
                    ->color('success')
                    ->visible(fn (IncidentReport $r) => self::isAdmin() && $r->status === ReportStatus::Sah && ! $r->disaster_event_id)
                    ->requiresConfirmation()
                    ->modalHeading('Jadikan riwayat kejadian?')
                    ->modalDescription('Data laporan disalin ke Riwayat Kejadian. Lengkapi jumlah korban dan kronologinya setelah ini.')
                    ->modalSubmitActionLabel('Ya, buat riwayat')
                    ->action(function (IncidentReport $report, $livewire) {
                        $event = DisasterEvent::create([
                            'title' => $report->kind->shortLabel().($report->hamlet ? ' di '.$report->hamlet : '').' ('.$report->code.')',
                            'hazard_type' => $report->kind->hazardType(),
                            'occurred_on' => $report->created_at->timezone('Asia/Makassar')->toDateString(),
                            'hamlet' => $report->hamlet,
                            'location' => $report->location,
                            'description' => trim($report->description."\n\n".($report->check_note ? 'Hasil cek kader: '.$report->check_note : '')),
                            'photo' => $report->photo,
                            'source' => 'Laporan warga '.$report->code.', dicek Karang Taruna',
                            'status' => VerificationStatus::Terverifikasi,
                        ]);
                        $report->update(['disaster_event_id' => $event->id]);

                        Notification::make()->title('Riwayat kejadian dibuat')->body('Lengkapi jumlah korban bila ada.')->success()->send();
                        $livewire->redirect(DisasterEventResource::getUrl('edit', ['record' => $event]));
                    }),
                Tables\Actions\EditAction::make()->label('Buka'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada laporan warga')
            ->emptyStateDescription('Laporan dari halaman "Lapor Kejadian" di web akan muncul di sini.')
            ->poll('60s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidentReports::route('/'),
            'edit' => Pages\EditIncidentReport::route('/{record}/edit'),
        ];
    }
}
