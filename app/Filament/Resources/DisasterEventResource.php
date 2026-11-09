<?php

namespace App\Filament\Resources;

use App\Enums\HazardType;
use App\Filament\Forms\Components\LocationPicker;
use App\Filament\Resources\Concerns\HasVerificationWorkflow;
use App\Filament\Resources\DisasterEventResource\Pages;
use App\Models\DisasterEvent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Riwayat kejadian bencana di desa dan jumlah korban (tanpa nama). */
class DisasterEventResource extends Resource
{
    use HasVerificationWorkflow;

    protected static ?string $model = DisasterEvent::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Laporan & Kejadian';

    protected static ?string $modelLabel = 'kejadian';

    protected static ?string $pluralModelLabel = 'Riwayat Kejadian';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $victim = fn (string $name, string $label) => TextInput::make($name)->label($label)->numeric()->minValue(0)->maxValue(100000)->default(0);

        return $form->schema([
            Section::make('Kejadian')
                ->columns(3)
                ->schema([
                    TextInput::make('title')->label('Judul kejadian')->required()->maxLength(160)->columnSpanFull()
                        ->placeholder("Contoh: Longsor menutup Jalan Poros Malino di Mala'lang"),
                    Select::make('hazard_type')->label('Jenis bencana')->options(HazardType::class)->required()->native(false),
                    DatePicker::make('occurred_on')->label('Tanggal kejadian')->required()->native(false)->displayFormat('d M Y')->maxDate(now()),
                    TextInput::make('hamlet')->label('Dusun / kampung')->maxLength(100),
                    Textarea::make('description')->label('Kronologi singkat')->rows(4)->columnSpanFull(),
                    Textarea::make('impact')->label('Dampak lain')->rows(2)->columnSpanFull()
                        ->placeholder('Contoh: jalan tertutup 6 jam, listrik padam, sawah tergenang'),
                ]),

            Section::make('Jumlah korban')
                ->description('Tulis angkanya saja. Nama korban tidak dicatat untuk menjaga privasi keluarga.')
                ->columns(5)
                ->schema([
                    $victim('deaths', 'Meninggal'),
                    $victim('injured', 'Luka'),
                    $victim('missing', 'Hilang'),
                    $victim('displaced', 'Mengungsi'),
                    $victim('houses_damaged', 'Rumah rusak'),
                ]),

            Section::make('Sumber dan bukti')
                ->columns(2)
                ->schema([
                    TextInput::make('source')->label('Sumber informasi')->maxLength(150)
                        ->placeholder('Contoh: BPBD Gowa, ANTARA, pemerintah desa'),
                    TextInput::make('source_url')->label('Tautan sumber')->url()->maxLength(500),
                    FileUpload::make('photo')->label('Foto')->image()->directory('kejadian')->maxSize(6144)
                        ->imageResizeMode('contain')->imageResizeTargetWidth('1600')->imageResizeUpscale(false)->columnSpanFull(),
                ]),

            Section::make('Titik kejadian di peta (opsional)')
                ->collapsible()
                ->schema([
                    LocationPicker::make('location')->label('Lokasi'),
                ]),

            static::verificationSection(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_on')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('title')->label('Kejadian')->searchable()->wrap(),
                TextColumn::make('hazard_type')->label('Jenis')->badge(),
                TextColumn::make('deaths')->label('Meninggal')->alignCenter(),
                TextColumn::make('displaced')->label('Mengungsi')->alignCenter(),
                ...static::verificationColumns(),
            ])
            ->defaultSort('occurred_on', 'desc')
            ->filters([
                static::statusFilter(),
                SelectFilter::make('hazard_type')->label('Jenis bencana')->options(HazardType::class),
            ])
            ->actions([
                ...static::verificationActions(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    static::bulkVerifyAction(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada riwayat kejadian');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisasterEvents::route('/'),
            'create' => Pages\CreateDisasterEvent::route('/create'),
            'edit' => Pages\EditDisasterEvent::route('/{record}/edit'),
        ];
    }
}
