<?php

namespace App\Filament\Resources;

use App\Enums\EducationCategory;
use App\Enums\EducationTopic;
use App\Filament\Resources\EducationContentResource\Pages;
use App\Models\EducationContent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class EducationContentResource extends Resource
{
    protected static ?string $model = EducationContent::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Informasi Publik';

    protected static ?string $modelLabel = 'konten edukasi';

    protected static ?string $pluralModelLabel = 'Edukasi';

    protected static ?int $navigationSort = 2;

    /** Jenis konten yang sedang dipilih di formulir (string saat dipilih, enum saat diedit). */
    private static function type(Get $get): ?EducationCategory
    {
        $value = $get('category');

        return $value instanceof EducationCategory ? $value : EducationCategory::tryFrom((string) $value);
    }

    private static function is(Get $get, EducationCategory ...$types): bool
    {
        return in_array(self::type($get), $types, true);
    }

    public static function form(Form $form): Form
    {
        $chosen = fn (Get $get): bool => self::type($get) !== null;

        return $form->schema([
            // Langkah 1: pilih jenis. Isian berikutnya menyesuaikan.
            Section::make('Jenis konten')
                ->description('Pilih dulu jenis konten yang mau dibuat. Isian di bawahnya akan menyesuaikan.')
                ->schema([
                    ToggleButtons::make('category')
                        ->hiddenLabel()
                        ->options(EducationCategory::class)
                        ->inline()
                        ->required()
                        ->live()
                        ->helperText(fn (Get $get) => self::type($get)?->hint()),
                ]),

            Section::make('Informasi dasar')
                ->columns(2)
                ->visible($chosen)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->placeholder(fn (Get $get) => match (self::type($get)) {
                            EducationCategory::Video => 'Contoh: Video simulasi evakuasi banjir',
                            EducationCategory::Infografis => 'Contoh: Isi tas siaga bencana',
                            EducationCategory::Panduan => 'Contoh: Yang harus dilakukan saat banjir datang',
                            default => 'Contoh: Mengenali tanda-tanda tanah longsor',
                        })
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull(),
                    Select::make('topic')
                        ->label('Topik')
                        ->options(EducationTopic::class)
                        ->default(EducationTopic::Umum->value)
                        ->required()
                        ->native(false),
                    Textarea::make('summary')
                        ->label('Ringkasan singkat')
                        ->helperText('Satu atau dua kalimat yang tampil di kartu daftar edukasi. Maksimal 300 karakter.')
                        ->maxLength(300)
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make(fn (Get $get) => match (self::type($get)) {
                EducationCategory::Video => 'Video',
                EducationCategory::Infografis => 'Gambar infografis',
                EducationCategory::Panduan => 'Isi panduan',
                default => 'Isi modul',
            })
                ->visible($chosen)
                ->schema([
                    // Video
                    TextInput::make('video_url')
                        ->label('Tautan video YouTube')
                        ->url()
                        ->placeholder('https://www.youtube.com/watch?v=...')
                        ->helperText('Unggah videonya ke YouTube dulu, lalu salin tautannya (tombol Bagikan → Salin) dan tempel di sini.')
                        ->required()
                        ->live(onBlur: true)
                        ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                            if (filled($value) && ! (new EducationContent(['video_url' => $value]))->youtubeId()) {
                                $fail('Tautan ini bukan tautan video YouTube.');
                            }
                        })
                        ->visible(fn (Get $get) => self::is($get, EducationCategory::Video)),
                    Placeholder::make('video_preview')
                        ->label('Pratinjau')
                        ->content(function (Get $get) {
                            $id = (new EducationContent(['video_url' => $get('video_url')]))->youtubeId();

                            return new HtmlString(
                                '<img src="https://img.youtube.com/vi/'.e($id).'/hqdefault.jpg" alt="Sampul video" '
                                .'style="width:100%;max-width:22rem;aspect-ratio:16/9;object-fit:cover;border-radius:.75rem">'
                            );
                        })
                        ->visible(fn (Get $get) => self::is($get, EducationCategory::Video)
                            && (new EducationContent(['video_url' => $get('video_url')]))->youtubeId() !== null),

                    // Gambar: wajib untuk infografis, sampul opsional untuk modul & panduan
                    FileUpload::make('image')
                        ->label(fn (Get $get) => self::is($get, EducationCategory::Infografis) ? 'Gambar infografis' : 'Gambar sampul (opsional)')
                        ->helperText(fn (Get $get) => self::is($get, EducationCategory::Infografis)
                            ? 'Format JPG atau PNG, maksimal 5 MB. Pastikan tulisannya terbaca di layar HP.'
                            : 'Tampil di atas isi. Maksimal 5 MB.')
                        ->image()
                        ->imageEditor()
                        ->directory('edukasi')
                        ->maxSize(5120)
                        ->required(fn (Get $get) => self::is($get, EducationCategory::Infografis))
                        ->visible(fn (Get $get) => self::type($get)?->uses('image') ?? false),

                    MarkdownEditor::make('body')
                        ->label(fn (Get $get) => match (self::type($get)) {
                            EducationCategory::Video => 'Keterangan video (opsional)',
                            EducationCategory::Infografis => 'Penjelasan (opsional)',
                            EducationCategory::Panduan => 'Langkah-langkah',
                            default => 'Isi materi',
                        })
                        ->placeholder(fn (Get $get) => match (self::type($get)) {
                            EducationCategory::Video => 'Apa isi video ini dan apa yang perlu diperhatikan warga.',
                            EducationCategory::Infografis => 'Tulis ulang isi gambar secara singkat, supaya tetap terbaca kalau gambar tidak termuat.',
                            EducationCategory::Panduan => "1. Matikan listrik dari sakelar utama.\n2. Bawa tas siaga.\n3. Menuju titik kumpul terdekat.",
                            default => 'Tulis materinya di sini. Gunakan judul kecil (H) untuk membagi bagian.',
                        })
                        ->helperText(fn (Get $get) => self::is($get, EducationCategory::Panduan)
                            ? 'Satu langkah per baris. Pakai tombol daftar bernomor di toolbar.'
                            : null)
                        ->required(fn (Get $get) => self::is($get, EducationCategory::Modul, EducationCategory::Panduan))
                        ->toolbarButtons(['bold', 'italic', 'heading', 'bulletList', 'orderedList', 'link', 'blockquote', 'undo', 'redo']),

                    FileUpload::make('attachment')
                        ->label('Berkas PDF untuk dicetak (opsional)')
                        ->helperText('Warga bisa mengunduh dan mencetaknya. Maksimal 10 MB.')
                        ->acceptedFileTypes(['application/pdf'])
                        ->directory('edukasi/berkas')
                        ->maxSize(10240)
                        ->visible(fn (Get $get) => self::type($get)?->uses('attachment') ?? false),
                ]),

            Section::make('Tampilan')
                ->columns(2)
                ->visible($chosen)
                ->schema([
                    Toggle::make('is_published')->label('Tampilkan di web')->default(true),
                    TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->helperText('Angka kecil tampil lebih dulu.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->wrap(),
                TextColumn::make('category')->label('Jenis')->badge(),
                TextColumn::make('topic')->label('Topik')->badge(),
                IconColumn::make('is_published')->label('Tampil')->boolean(),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('category')->label('Jenis')->options(EducationCategory::class),
                SelectFilter::make('topic')->label('Topik')->options(EducationTopic::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListEducationContents::route('/'),
            'create' => Pages\CreateEducationContent::route('/create'),
            'edit' => Pages\EditEducationContent::route('/{record}/edit'),
        ];
    }
}
