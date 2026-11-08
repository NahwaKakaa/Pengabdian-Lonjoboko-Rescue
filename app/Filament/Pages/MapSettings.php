<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\BoundaryPicker;
use App\Filament\Forms\Components\LocationPicker;
use App\Models\Setting;
use App\Support\VillageBoundary;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Batas wilayah desa, tampilan awal peta, dan daftar sumber peringatan resmi.
 *
 * @property Form $form
 */
class MapSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Peta & Sumber Resmi';

    protected static ?string $title = 'Pengaturan Peta & Sumber Resmi';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.village-profile';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'village_boundary' => VillageBoundary::ring(),
            'map_center' => config('lonjoboko.map.center'),
            'map_zoom' => (string) config('lonjoboko.map.zoom'),
            'official_sources' => config('lonjoboko.official_sources'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Batas wilayah desa')
                    ->description('Garis batas desa di Peta Risiko, Jalur Evakuasi, dan poster. Batas bawaan adalah perkiraan dari Google Maps; ganti dengan batas resmi bila sudah ada.')
                    ->schema([
                        BoundaryPicker::make('village_boundary')->hiddenLabel(),
                        FileUpload::make('boundary_file')
                            ->label('Atau unggah file batas resmi')
                            ->helperText('File GeoJSON (.geojson/.json) atau KML dari Google My Maps / Google Earth. Setelah diunggah, batas langsung tampil di peta di atas; periksa lalu tekan Simpan.')
                            ->disk('local')
                            ->directory('batas-desa')
                            ->maxSize(2048)
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set) {
                                $file = is_array($state) ? reset($state) : $state;

                                if (! $file instanceof TemporaryUploadedFile) {
                                    return;
                                }

                                $ring = VillageBoundary::ringFromFile((string) $file->get());
                                $set('boundary_file', null);

                                if ($ring === null) {
                                    Notification::make()
                                        ->title('Batas tidak terbaca')
                                        ->body('File tidak berisi poligon. Pastikan yang diunggah adalah area/batas (bukan titik atau garis).')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $set('village_boundary', $ring);

                                Notification::make()
                                    ->title('Batas dari file sudah dimuat')
                                    ->body(count($ring).' titik. Periksa di peta, lalu tekan Simpan.')
                                    ->success()
                                    ->send();
                            }),
                    ]),

                Section::make('Tampilan awal peta')
                    ->description('Titik tengah dan perbesaran saat peta pertama dibuka (bila batas desa tidak tersedia), dan titik awal saat admin menandai lokasi.')
                    ->columns(3)
                    ->schema([
                        LocationPicker::make('map_center')
                            ->label('Titik tengah peta')
                            ->helperText('Biasanya kantor desa.')
                            ->required()
                            ->columnSpan(2),
                        Select::make('map_zoom')
                            ->label('Perbesaran awal')
                            ->options([
                                '12' => '12 · satu kecamatan',
                                '13' => '13 · beberapa desa',
                                '14' => '14 · satu desa (disarankan)',
                                '15' => '15 · beberapa dusun',
                                '16' => '16 · satu dusun',
                            ])
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Sumber peringatan dini resmi')
                    ->description('Tampil di halaman Info Darurat. Misalnya BMKG, BPBD Kabupaten Gowa, atau kanal resmi kecamatan.')
                    ->schema([
                        Repeater::make('official_sources')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('name')->label('Nama')->required()->maxLength(60),
                                TextInput::make('url')->label('Alamat web')->url()->required()->maxLength(255)->placeholder('https://'),
                                Textarea::make('description')->label('Keterangan')->rows(2)->maxLength(200)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->addActionLabel('Tambah sumber')
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')->label('Simpan')->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $ring = VillageBoundary::normalizeRing($state['village_boundary'] ?? null);
        $isDefault = $ring === null || $ring === VillageBoundary::defaultRing();

        Setting::putMany([
            // Kosong = memakai batas bawaan
            'village_boundary' => $isDefault ? null : json_encode($ring),
            'map_center_lat' => (string) ($state['map_center']['lat'] ?? config('lonjoboko.map.center.lat')),
            'map_center_lng' => (string) ($state['map_center']['lng'] ?? config('lonjoboko.map.center.lng')),
            'map_zoom' => (string) ($state['map_zoom'] ?? 14),
            'official_sources' => json_encode(array_values(array_map(fn ($s) => [
                'name' => trim((string) ($s['name'] ?? '')),
                'url' => trim((string) ($s['url'] ?? '')),
                'description' => trim((string) ($s['description'] ?? '')),
            ], $state['official_sources'] ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        Notification::make()
            ->title('Pengaturan peta disimpan')
            ->body($isDefault ? 'Batas desa memakai batas bawaan.' : 'Batas desa baru sudah dipakai di peta dan poster.')
            ->success()
            ->send();
    }
}
