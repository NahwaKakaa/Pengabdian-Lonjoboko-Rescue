<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\LocationPicker;
use App\Filament\Resources\HamletPopulationResource\Pages;
use App\Models\HamletPopulation;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Jumlah penduduk dan warga rentan per dusun (angka saja, tanpa nama). */
class HamletPopulationResource extends Resource
{
    protected static ?string $model = HamletPopulation::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Data Desa';

    protected static ?string $modelLabel = 'data dusun';

    protected static ?string $pluralModelLabel = 'Data Penduduk';

    protected static ?int $navigationSort = 1;

    private static function number(string $name, string $label): TextInput
    {
        return TextInput::make($name)->label($label)->numeric()->minValue(0)->maxValue(100000)->suffix('orang');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dusun')
                ->description('Isi dengan angka resmi dari kantor desa atau BPS. Kolom yang belum diketahui boleh dikosongkan.')
                ->columns(3)
                ->schema([
                    TextInput::make('name')->label('Nama dusun')->required()->maxLength(100)->placeholder('Contoh: Tombongi'),
                    self::number('population', 'Jumlah penduduk'),
                    TextInput::make('households')->label('Jumlah KK')->numeric()->minValue(0)->maxValue(50000)->suffix('KK'),
                    self::number('male', 'Laki-laki'),
                    self::number('female', 'Perempuan'),
                ]),

            Section::make('Warga rentan')
                ->description('Didahulukan saat evakuasi. Tulis jumlahnya saja, jangan nama atau alamat.')
                ->columns(4)
                ->schema([
                    self::number('elderly', 'Lansia (60+ tahun)'),
                    self::number('toddlers', 'Balita'),
                    self::number('pregnant', 'Ibu hamil'),
                    self::number('disabled', 'Penyandang disabilitas'),
                    Textarea::make('note')->label('Catatan')->rows(2)->columnSpanFull()
                        ->placeholder('Contoh: banyak lansia tinggal di tepi sungai'),
                ]),

            Section::make('Titik dusun di peta')
                ->description('Pilih kira-kira pusat permukiman dusun. Dipakai untuk lapisan "Warga rentan" di Peta Risiko.')
                ->collapsible()
                ->schema([
                    LocationPicker::make('location')->label('Lokasi'),
                ]),

            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Dusun')->searchable()->weight('bold'),
                TextColumn::make('population')->label('Penduduk')->numeric(locale: 'id')->placeholder('-'),
                TextColumn::make('households')->label('KK')->numeric(locale: 'id')->placeholder('-'),
                TextColumn::make('elderly')->label('Lansia')->placeholder('-'),
                TextColumn::make('toddlers')->label('Balita')->placeholder('-'),
                TextColumn::make('pregnant')->label('Ibu hamil')->placeholder('-'),
                TextColumn::make('disabled')->label('Disabilitas')->placeholder('-'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Belum ada data penduduk')
            ->emptyStateDescription('Tambahkan satu baris untuk setiap dusun: Bontoloe, Galesong, Tombongi, Lantaboko.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHamletPopulations::route('/'),
            'create' => Pages\CreateHamletPopulation::route('/create'),
            'edit' => Pages\EditHamletPopulation::route('/{record}/edit'),
        ];
    }
}
