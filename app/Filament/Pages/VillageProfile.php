<?php

namespace App\Filament\Pages;

use App\Filament\Resources\EmergencyContactResource;
use App\Models\Setting;
use App\Support\SafetyGuide;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

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
        'whatsapp_number',
        'whatsapp_label',
        'maps_url',
        'landscape_note',
        'safety_first_steps',
        'safety_flood_steps',
        'safety_landslide_steps',
        'safety_popup_message',
        'photo_hero',
        'photo_profile',
        'photo_profile_caption',
        'photo_landscape',
        'photo_landscape_caption',
    ];

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $values = collect(self::KEYS)->mapWithKeys(fn (string $key) => [$key => Setting::allValues()[$key] ?? null])->all();

        // Tampilkan teks yang sedang dipakai web supaya admin tinggal menyunting.
        $values['safety_first_steps'] = SafetyGuide::firstSteps();
        $values['safety_flood_steps'] = implode("\n", SafetyGuide::floodSteps());
        $values['safety_landslide_steps'] = implode("\n", SafetyGuide::landslideSteps());
        $values['safety_popup_message'] = SafetyGuide::popupMessage();

        $this->form->fill($values);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Tabs::make('Profil desa')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Identitas & kontak')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                Section::make('Identitas desa')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('village_name')->label('Nama desa')->required()->maxLength(100),
                                        TextInput::make('district')->label('Kecamatan')->maxLength(100),
                                        TextInput::make('regency')->label('Kabupaten')->maxLength(100),
                                        TextInput::make('office_address')->label('Alamat kantor desa')->maxLength(255)->columnSpan(2),
                                        TextInput::make('office_phone')->label('Telepon kantor desa')->tel()->maxLength(30),
                                        TextInput::make('landscape_note')
                                            ->label('Keterangan bentang alam')
                                            ->maxLength(90)
                                            ->placeholder('Lereng perbukitan & persawahan Parangloe')
                                            ->helperText('Tampil di bar atas dan bagian paling bawah web. Tulis yang benar-benar sesuai keadaan desa.')
                                            ->columnSpanFull(),
                                    ]),
                                    Section::make('Kontak & lokasi di footer web')
                                        ->description(new HtmlString(
                                            'Tampil di bagian bawah setiap halaman publik. Daftar nomor darurat di footer diatur di menu '
                                            .'<a href="'.EmergencyContactResource::getUrl().'" class="font-semibold text-primary-600 underline dark:text-primary-400">Kontak Darurat</a>.'
                                        ))
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('whatsapp_number')
                                                ->label('Nomor WhatsApp desa')
                                                ->tel()
                                                ->maxLength(30)
                                                ->placeholder('0812xxxxxxxx')
                                                ->helperText('Untuk warga melapor atau bertanya. Kalau kosong, dipakai kontak darurat pertama yang punya WhatsApp.'),
                                            TextInput::make('whatsapp_label')
                                                ->label('Nama pemegang nomor')
                                                ->maxLength(80)
                                                ->placeholder('Posko Desa Lonjoboko'),
                                            TextInput::make('maps_url')
                                                ->label('Tautan Google Maps kantor desa (opsional)')
                                                ->url()
                                                ->maxLength(500)
                                                ->placeholder('https://maps.app.goo.gl/...')
                                                ->helperText('Buka lokasi kantor desa di Google Maps, tekan Bagikan, lalu salin tautannya ke sini.')
                                                ->columnSpan(2),
                                        ]),
                            ]),

                        Tab::make('Teks Beranda')
                            ->icon('heroicon-m-document-text')
                            ->schema([
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
                            ]),

                        Tab::make('Panduan keselamatan')
                            ->icon('heroicon-m-lifebuoy')
                            ->schema([
                                Section::make('Tiga hal pertama yang harus dilakukan')
                                    ->description('Tampil di Beranda. Urutan bisa diubah dengan menyeret.')
                                    ->schema([
                                        Repeater::make('safety_first_steps')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextInput::make('title')->label('Judul langkah')->required()->maxLength(60),
                                                Textarea::make('body')->label('Penjelasan')->rows(2)->maxLength(240),
                                            ])
                                            ->minItems(3)
                                            ->maxItems(3)
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable()
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                            ->collapsible(),
                                    ]),
                                Section::make('Langkah saat banjir dan tanah longsor')
                                    ->description('Tampil di halaman Jalur Evakuasi dan poster cetak. Satu langkah per baris. Di poster hanya 4 langkah pertama yang dicetak (maksimal 2 baris per langkah).')
                                    ->columns(2)
                                    ->schema([
                                        Textarea::make('safety_flood_steps')->label('Saat banjir')->rows(7)->helperText('Kosongkan untuk memakai teks bawaan.'),
                                        Textarea::make('safety_landslide_steps')->label('Saat tanah longsor')->rows(7)->helperText('Kosongkan untuk memakai teks bawaan.'),
                                    ]),
                                Section::make('Pesan di popup keadaan darurat')
                                    ->schema([
                                        Textarea::make('safety_popup_message')
                                            ->hiddenLabel()
                                            ->rows(2)
                                            ->maxLength(220)
                                            ->helperText('Muncul di popup saat web dibuka ketika ada peringatan tingkat Darurat.'),
                                    ]),
                            ]),

                        Tab::make('Foto desa')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Section::make('Foto latar atas')
                                    ->description('Dipakai di Beranda dan header setiap halaman. Pada tampilan malam, foto ini otomatis digelapkan dan diberi langit berbintang. Kalau kosong, memakai ilustrasi bawaan.')
                                    ->schema([
                                        self::photoUpload('photo_hero')
                                            ->hiddenLabel()
                                            ->helperText('Foto mendatar (landscape), sebaiknya pemandangan desa dengan sedikit langit. Maksimal 6 MB; foto besar otomatis diperkecil sebelum dikirim.'),
                                    ]),
                                Section::make('Foto di kartu profil desa')
                                    ->columns(2)
                                    ->schema([
                                        self::photoUpload('photo_profile')->label('Foto'),
                                        TextInput::make('photo_profile_caption')->label('Keterangan foto')->maxLength(120)->placeholder('Contoh: Persawahan Dusun Bontote\'ne'),
                                    ]),
                                Section::make('Foto di bagian karakteristik wilayah')
                                    ->columns(2)
                                    ->schema([
                                        self::photoUpload('photo_landscape')->label('Foto'),
                                        TextInput::make('photo_landscape_caption')->label('Keterangan foto')->maxLength(120)->placeholder('Contoh: Lereng perbukitan di utara desa'),
                                    ]),
                            ]),

                        Tab::make('Info darurat')
                            ->icon('heroicon-m-exclamation-triangle')
                            ->schema([
                                    Section::make('Menu Informasi Darurat')
                                        ->schema([
                                            Textarea::make('report_instructions')
                                                ->label('Cara melaporkan kejadian bencana')
                                                ->rows(5)
                                                ->helperText('Satu langkah per baris. Akan ditampilkan sebagai daftar bernomor.'),
                                        ]),
                            ]),
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

    private static function photoUpload(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->imageEditor()
            ->directory('desa')
            ->maxSize(6144)
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth('1920')
            ->imageResizeUpscale(false);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $state['safety_first_steps'] = json_encode(array_values($state['safety_first_steps'] ?? []), JSON_UNESCAPED_UNICODE);

        Setting::putMany($state);

        Notification::make()->title('Profil desa disimpan')->success()->send();
    }
}
