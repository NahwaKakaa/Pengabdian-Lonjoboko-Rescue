<?php

namespace App\Filament\Resources;

use App\Enums\HazardType;
use App\Enums\RiskLevel;
use App\Filament\Forms\Components\LocationPicker;
use App\Filament\Resources\Concerns\HasVerificationWorkflow;
use App\Filament\Resources\HazardZoneResource\Pages;
use App\Models\HazardZone;
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

class HazardZoneResource extends Resource
{
    use HasVerificationWorkflow;

    protected static ?string $model = HazardZone::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Peta & Evakuasi';

    protected static ?string $modelLabel = 'lokasi rawan';

    protected static ?string $pluralModelLabel = 'Lokasi Rawan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Keterangan lokasi')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama lokasi')
                        ->placeholder('Contoh: Tebing belakang SD, Dusun ...')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                    Select::make('hazard_type')
                        ->label('Jenis bencana')
                        ->options(HazardType::class)
                        ->required()
                        ->native(false),
                    Select::make('risk_level')
                        ->label('Tingkat kerawanan')
                        ->options(RiskLevel::class)
                        ->default(RiskLevel::Sedang->value)
                        ->required()
                        ->native(false),
                    TextInput::make('radius_m')
                        ->label('Perkiraan luas area (radius)')
                        ->numeric()
                        ->minValue(10)
                        ->maxValue(2000)
                        ->default(100)
                        ->suffix('meter')
                        ->helperText('Seberapa jauh dari titik ini area yang rawan. Boleh perkiraan.'),
                    Textarea::make('description')
                        ->label('Keterangan / tanda-tanda yang terlihat')
                        ->placeholder('Contoh: muncul retakan tanah setelah hujan deras, air sungai pernah naik sampai lutut pada 2025.')
                        ->rows(3)
                        ->columnSpanFull(),
                    FileUpload::make('photo')
                        ->label('Foto lokasi')
                        ->image()
                        ->directory('lokasi-rawan')
                        ->maxSize(4096)
                        ->helperText('Opsional. Maksimal 4 MB.')
                        ->columnSpanFull(),
                ]),

            Section::make('Titik di peta')
                ->schema([
                    LocationPicker::make('location')
                        ->label('Lokasi')
                        ->required(),
                ]),

            static::verificationSection(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama lokasi')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('hazard_type')
                    ->label('Jenis')
                    ->badge(),
                TextColumn::make('risk_level')
                    ->label('Kerawanan')
                    ->badge()
                    ->sortable(),
                ...static::verificationColumns(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                static::statusFilter(),
                SelectFilter::make('hazard_type')->label('Jenis bencana')->options(HazardType::class),
                SelectFilter::make('risk_level')->label('Kerawanan')->options(RiskLevel::class),
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
            ->emptyStateHeading('Belum ada lokasi rawan')
            ->emptyStateDescription('Tambahkan lokasi rawan banjir atau longsor hasil pengamatan di lapangan.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHazardZones::route('/'),
            'create' => Pages\CreateHazardZone::route('/create'),
            'edit' => Pages\EditHazardZone::route('/{record}/edit'),
        ];
    }
}
