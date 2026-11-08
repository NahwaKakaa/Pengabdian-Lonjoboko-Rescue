<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\VerificationStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * Potongan form, kolom, dan aksi verifikasi yang dipakai bersama
 * oleh semua resource data peta.
 */
trait HasVerificationWorkflow
{
    protected static function isAdmin(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Menunggu verifikasi';
    }

    public static function verificationSection(): Section
    {
        return Section::make('Verifikasi')
            ->schema([
                Placeholder::make('kader_notice')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        'Data yang Anda simpan akan berstatus <strong>Menunggu Verifikasi</strong> dan baru tampil di web setelah disetujui admin desa.'
                    ))
                    ->visible(fn () => ! static::isAdmin()),

                Placeholder::make('rejection_note')
                    ->label('Catatan dari admin')
                    ->content(fn (?Model $record) => $record?->review_note)
                    ->visible(fn (?Model $record) => ! static::isAdmin()
                        && $record?->status === VerificationStatus::Ditolak
                        && filled($record?->review_note)),

                Select::make('status')
                    ->label('Status')
                    ->options(VerificationStatus::class)
                    ->default(VerificationStatus::Terverifikasi->value)
                    ->required()
                    ->native(false)
                    ->live()
                    ->helperText('Hanya data berstatus Terverifikasi yang tampil di halaman publik.')
                    ->visible(fn () => static::isAdmin()),

                Textarea::make('review_note')
                    ->label('Catatan untuk kader')
                    ->rows(2)
                    ->placeholder('Contoh: titik kurang tepat, mohon dicek ulang di lapangan.')
                    ->visible(fn () => static::isAdmin()),

                Placeholder::make('verified_info')
                    ->label('Diverifikasi')
                    ->content(fn (?Model $record) => $record?->verifier
                        ? $record->verifier->name.' · '.$record->verified_at?->translatedFormat('d M Y H:i')
                        : '-')
                    ->visible(fn (?Model $record) => $record?->isVerified() ?? false),
            ])
            ->collapsible();
    }

    /** @return array<int, TextColumn> */
    public static function verificationColumns(): array
    {
        return [
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable(),
            TextColumn::make('creator.name')
                ->label('Diinput oleh')
                ->placeholder('-')
                ->toggleable(),
            TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->since()
                ->sortable()
                ->toggleable(),
        ];
    }

    public static function statusFilter(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Status')
            ->options(VerificationStatus::class);
    }

    /** @return array<int, Action> */
    public static function verificationActions(): array
    {
        return [
            Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi data ini?')
                ->modalDescription('Data akan langsung tampil di halaman publik.')
                ->visible(fn (Model $record) => static::isAdmin() && ! $record->isVerified())
                ->action(function (Model $record) {
                    $record->update(['status' => VerificationStatus::Terverifikasi]);

                    Notification::make()->title('Data terverifikasi dan tampil di web')->success()->send();
                }),

            Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('review_note')
                        ->label('Alasan / catatan untuk kader')
                        ->required()
                        ->rows(3),
                ])
                ->visible(fn (Model $record) => static::isAdmin() && $record->status !== VerificationStatus::Ditolak)
                ->action(function (Model $record, array $data) {
                    $record->update([
                        'status' => VerificationStatus::Ditolak,
                        'review_note' => $data['review_note'],
                    ]);

                    Notification::make()->title('Data ditolak')->body('Kader bisa melihat catatan Anda saat membuka data ini.')->warning()->send();
                }),
        ];
    }

    public static function bulkVerifyAction(): BulkAction
    {
        return BulkAction::make('bulkVerify')
            ->label('Verifikasi terpilih')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn () => static::isAdmin())
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $records->each->update(['status' => VerificationStatus::Terverifikasi]);

                Notification::make()->title($records->count().' data terverifikasi')->success()->send();
            });
    }
}
