<?php

namespace App\Filament\Resources;

use App\Enums\AnnouncementLevel;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms\Components\DateTimePicker;
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

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Informasi Publik';

    protected static ?string $modelLabel = 'informasi';

    protected static ?string $pluralModelLabel = 'Informasi & Peringatan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull(),
                    Select::make('level')
                        ->label('Jenis')
                        ->options(AnnouncementLevel::class)
                        ->default(AnnouncementLevel::Informasi->value)
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Peringatan dan Darurat tampil menonjol di Beranda selama masih berlaku.'),
                    Toggle::make('is_published')
                        ->label('Tampilkan di web')
                        ->default(true)
                        ->inline(false),
                    Textarea::make('body')
                        ->label('Isi informasi')
                        ->required()
                        ->rows(6)
                        ->columnSpanFull(),
                    TextInput::make('source')
                        ->label('Sumber')
                        ->placeholder('Contoh: BMKG, BPBD Kab. Gowa, Pemerintah Desa')
                        ->maxLength(150)
                        ->required(fn (Get $get) => in_array($get('level'), [AnnouncementLevel::Waspada->value, AnnouncementLevel::Darurat->value], true))
                        ->helperText('Wajib untuk peringatan. Teruskan informasi dari instansi resmi, jangan membuat prakiraan sendiri.'),
                    TextInput::make('source_url')
                        ->label('Tautan sumber')
                        ->url()
                        ->maxLength(255),
                    DateTimePicker::make('published_at')
                        ->label('Waktu terbit')
                        ->default(now())
                        ->seconds(false),
                    DateTimePicker::make('expires_at')
                        ->label('Berlaku sampai')
                        ->seconds(false)
                        ->helperText('Kosongkan bila tidak ada batas waktu.')
                        ->after('published_at'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->wrap(),
                TextColumn::make('level')->label('Jenis')->badge(),
                IconColumn::make('is_published')->label('Tampil')->boolean(),
                TextColumn::make('published_at')->label('Terbit')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('expires_at')->label('Berlaku sampai')->dateTime('d M Y H:i')->placeholder('-')->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('level')->label('Jenis')->options(AnnouncementLevel::class),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
