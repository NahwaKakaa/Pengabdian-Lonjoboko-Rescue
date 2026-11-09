<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Kegiatan kesiapsiagaan Karang Taruna: dicatat oleh kader, tampil di halaman Relawan. */
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Informasi Publik';

    protected static ?string $modelLabel = 'kegiatan';

    protected static ?string $pluralModelLabel = 'Kegiatan Karang Taruna';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                TextInput::make('title')->label('Nama kegiatan')->required()->maxLength(150)->columnSpanFull()
                    ->placeholder('Contoh: Simulasi evakuasi longsor di Dusun Tombongi'),
                DatePicker::make('held_on')->label('Tanggal')->required()->native(false)->displayFormat('d M Y'),
                TextInput::make('place')->label('Tempat')->maxLength(150)->placeholder('Contoh: Halaman Kantor Desa'),
                Textarea::make('description')->label('Cerita singkat kegiatan')->rows(4)->columnSpanFull()
                    ->helperText('Apa yang dilakukan, siapa saja yang ikut, dan hasilnya.'),
                FileUpload::make('photo')->label('Foto utama')->image()->directory('kegiatan')->maxSize(6144)
                    ->imageResizeMode('contain')->imageResizeTargetWidth('1600')->imageResizeUpscale(false)
                    ->helperText('Tampil di kartu kegiatan halaman Relawan.'),
                FileUpload::make('gallery')->label('Foto lainnya')->image()->multiple()->reorderable()->maxFiles(8)
                    ->directory('kegiatan')->maxSize(6144)->panelLayout('grid')
                    ->imageResizeMode('contain')->imageResizeTargetWidth('1600')->imageResizeUpscale(false)
                    ->helperText('Paling banyak 8 foto. Tampil di halaman detail kegiatan.'),
                Toggle::make('is_published')->label('Tampilkan di web')->default(true)->inline(false)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')->label('')->square(),
                TextColumn::make('held_on')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('title')->label('Kegiatan')->searchable()->wrap(),
                TextColumn::make('place')->label('Tempat')->placeholder('-'),
                TextColumn::make('creator.name')->label('Dicatat oleh')->placeholder('-'),
                IconColumn::make('is_published')->label('Tampil')->boolean(),
            ])
            ->defaultSort('held_on', 'desc')
            ->actions([
                Tables\Actions\Action::make('lihat')->label('Lihat di web')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Activity $record) => route('activity.show', $record), shouldOpenInNewTab: true)
                    ->visible(fn (Activity $record) => $record->is_published),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Belum ada kegiatan')
            ->emptyStateDescription('Catat sosialisasi, simulasi evakuasi, atau pengecekan jalur yang dilakukan Karang Taruna.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
