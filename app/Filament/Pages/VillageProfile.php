<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Profil desa dan teks umum yang tampil di halaman publik.
 *
 * @property Form $form
 */
class VillageProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Profil Desa';

    protected static ?string $title = 'Profil Desa & Teks Web';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.village-profile';

    /** Kunci pengaturan yang dikelola di halaman ini. */
    public const KEYS = [
        'village_name',
        'district',
        'regency',
        'village_profile',
        'program_description',
        'population_note',
        'report_instructions',
        'office_address',
        'office_phone',
    ];

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $values = Setting::allValues();

        $this->form->fill(collect(self::KEYS)->mapWithKeys(fn (string $key) => [$key => $values[$key] ?? null])->all());
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Identitas desa')
                    ->columns(3)
                    ->schema([
                        TextInput::make('village_name')->label('Nama desa')->required()->maxLength(100),
                        TextInput::make('district')->label('Kecamatan')->maxLength(100),
                        TextInput::make('regency')->label('Kabupaten')->maxLength(100),
                        TextInput::make('office_address')->label('Alamat kantor desa')->maxLength(255)->columnSpan(2),
                        TextInput::make('office_phone')->label('Telepon kantor desa')->tel()->maxLength(30),
                    ]),

                Section::make('Teks di Beranda')
                    ->schema([
                        Textarea::make('village_profile')
                            ->label('Profil singkat desa')
                            ->rows(4)
                            ->helperText('Gambaran wilayah: letak, jumlah dusun, sungai atau lereng di sekitar desa.'),
                        Textarea::make('program_description')
                            ->label('Tentang program LONJOBOKO RESCUE')
                            ->rows(4),
                        Textarea::make('population_note')
                            ->label('Catatan kelompok rentan')
                            ->rows(3)
                            ->helperText('Tulis secara umum per wilayah, misalnya "banyak lansia di Dusun A". Jangan menulis nama atau alamat perorangan.'),
                    ]),

                Section::make('Menu Informasi Darurat')
                    ->schema([
                        Textarea::make('report_instructions')
                            ->label('Cara melaporkan kejadian bencana')
                            ->rows(5)
                            ->helperText('Satu langkah per baris. Akan ditampilkan sebagai daftar bernomor.'),
                    ]),
            ]);
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        Setting::putMany($this->form->getState());

        Notification::make()->title('Profil desa disimpan')->success()->send();
    }
}
