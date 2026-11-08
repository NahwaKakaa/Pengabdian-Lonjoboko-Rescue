<?php

namespace App\Filament\Resources;

use App\Enums\RouteHazard;
use App\Filament\Forms\Components\RoutePicker;
use App\Filament\Resources\Concerns\HasVerificationWorkflow;
use App\Filament\Resources\EvacuationRouteResource\Pages;
use App\Models\AssemblyPoint;
use App\Models\EvacuationRoute;
use App\Models\HazardZone;
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

class EvacuationRouteResource extends Resource
{
    use HasVerificationWorkflow;

    protected static ?string $model = EvacuationRoute::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationGroup = 'Peta & Evakuasi';

    protected static ?string $modelLabel = 'jalur evakuasi';

    protected static ?string $pluralModelLabel = 'Jalur Evakuasi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Keterangan jalur')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama jalur')
                        ->placeholder('Contoh: Jalur Dusun A menuju Lapangan Desa')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                    Select::make('hazard_type')
                        ->label('Dipakai saat')
                        ->options(RouteHazard::class)
                        ->default(RouteHazard::Semua->value)
                        ->required()
                        ->native(false),
                    Select::make('assembly_point_id')
                        ->label('Tujuan (titik kumpul)')
                        ->relationship('assemblyPoint', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih titik kumpul'),
                    Textarea::make('description')
                        ->label('Petunjuk jalur')
                        ->placeholder('Contoh: lewat jalan setapak di samping masjid, hindari jembatan kayu saat air naik.')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Gambar jalur di peta')
                ->description('Titik hijau adalah titik kumpul, titik oranye adalah lokasi rawan. Hindari melewati lokasi rawan.')
                ->schema([
                    RoutePicker::make('path')
                        ->label('Jalur')
                        ->referencePoints(fn () => static::referencePoints())
                        ->required(),
                ]),

            static::verificationSection(),
        ]);
    }

    /** @return array<int, array{lat: float, lng: float, label: string, kind: string}> */
    protected static function referencePoints(): array
    {
        $safe = AssemblyPoint::query()
            ->where('status', '!=', 'ditolak')
            ->get()
            ->filter->hasValidLocation()
            ->map(fn (AssemblyPoint $p) => ['lat' => $p->lat, 'lng' => $p->lng, 'label' => $p->name, 'kind' => 'safe']);

        $hazards = HazardZone::query()
            ->where('status', '!=', 'ditolak')
            ->get()
            ->filter->hasValidLocation()
            ->map(fn (HazardZone $z) => ['lat' => $z->lat, 'lng' => $z->lng, 'label' => 'Rawan: '.$z->name, 'kind' => 'hazard']);

        return $safe->concat($hazards)->values()->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama jalur')->searchable()->wrap(),
                TextColumn::make('hazard_type')->label('Dipakai saat')->badge(),
                TextColumn::make('assemblyPoint.name')->label('Tujuan')->placeholder('-')->wrap(),
                TextColumn::make('length')
                    ->label('Panjang')
                    ->state(fn (EvacuationRoute $record) => $record->lengthInMeters())
                    ->formatStateUsing(fn (int $state) => $state >= 1000
                        ? number_format($state / 1000, 2, ',', '.').' km'
                        : number_format($state, 0, ',', '.').' m'),
                ...static::verificationColumns(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                static::statusFilter(),
                SelectFilter::make('hazard_type')->label('Dipakai saat')->options(RouteHazard::class),
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
            ->emptyStateHeading('Belum ada jalur evakuasi')
            ->emptyStateDescription('Sebaiknya tambahkan titik kumpul terlebih dahulu, lalu gambar jalur menuju titik kumpul tersebut.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvacuationRoutes::route('/'),
            'create' => Pages\CreateEvacuationRoute::route('/create'),
            'edit' => Pages\EditEvacuationRoute::route('/{record}/edit'),
        ];
    }
}
