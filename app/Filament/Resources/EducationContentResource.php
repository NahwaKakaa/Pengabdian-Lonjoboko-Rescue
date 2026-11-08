<?php

namespace App\Filament\Resources;

use App\Enums\EducationCategory;
use App\Enums\EducationTopic;
use App\Filament\Resources\EducationContentResource\Pages;
use App\Models\EducationContent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EducationContentResource extends Resource
{
    protected static ?string $model = EducationContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Informasi Publik';

    protected static ?string $modelLabel = 'konten edukasi';

    protected static ?string $pluralModelLabel = 'Edukasi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Konten')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull(),
                    Select::make('category')
                        ->label('Jenis konten')
                        ->options(EducationCategory::class)
                        ->required()
                        ->native(false)
                        ->live(),
                    Select::make('topic')
                        ->label('Topik')
                        ->options(EducationTopic::class)
                        ->default(EducationTopic::Umum->value)
                        ->required()
                        ->native(false),
                    Textarea::make('summary')
                        ->label('Ringkasan singkat')
                        ->helperText('Tampil di kartu daftar edukasi. Maksimal 300 karakter.')
                        ->maxLength(300)
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('video_url')
                        ->label('Tautan video YouTube')
                        ->url()
                        ->placeholder('https://www.youtube.com/watch?v=...')
                        ->helperText('Unggah video ke YouTube, lalu tempel tautannya di sini supaya hosting tetap ringan.')
                        ->required(fn (Get $get) => $get('category') === EducationCategory::Video->value)
                        ->columnSpanFull(),
                    MarkdownEditor::make('body')
                        ->label('Isi')
                        ->toolbarButtons(['bold', 'italic', 'heading', 'bulletList', 'orderedList', 'link', 'blockquote', 'undo', 'redo'])
                        ->columnSpanFull(),
                ]),

            Section::make('Gambar & berkas')
                ->columns(2)
                ->schema([
                    FileUpload::make('image')
                        ->label('Gambar sampul / infografis')
                        ->image()
                        ->directory('edukasi')
                        ->maxSize(4096),
                    FileUpload::make('attachment')
                        ->label('Berkas modul (PDF)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->directory('edukasi/berkas')
                        ->maxSize(10240)
                        ->helperText('Maksimal 10 MB.'),
                ]),

            Section::make('Tampilan')
                ->columns(2)
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
