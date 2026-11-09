<?php

namespace App\Filament\Resources;

use App\Enums\FacilityCategory;
use App\Filament\Forms\Components\LocationPicker;
use App\Filament\Resources\Concerns\HasVerificationWorkflow;
use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    use HasVerificationWorkflow;

    protected static ?string $model = Facility::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Peta & Evakuasi';

    protected static ?string $modelLabel = 'fasilitas';

    protected static ?string $pluralModelLabel = 'Fasilitas Umum & Kesehatan';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Keterangan')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama fasilitas')
                        ->placeholder('Contoh: Puskesmas Pembantu Lonjoboko')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                    Select::make('category')
                        ->label('Kategori')
                        ->options(FacilityCategory::class)
                        ->required()
                        ->native(false),
                    TextInput::make('phone')
                        ->label('Nomor telepon')
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('opening_hours')
                        ->label('Jam layanan')
                        ->maxLength(120)
                        ->placeholder('Contoh: Senin–Sabtu 08.00–14.00, IGD 24 jam')
                        ->columnSpanFull(),
                    TextInput::make('address')
                        ->label('Alamat / patokan')
                        ->maxLength(255)
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
                TextColumn::make('name')->label('Nama fasilitas')->searchable()->wrap(),
                TextColumn::make('category')->label('Kategori')->badge(),
                TextColumn::make('phone')->label('Telepon')->placeholder('-')->copyable(),
                TextColumn::make('opening_hours')->label('Jam layanan')->placeholder('-')->toggleable(),
                ...static::verificationColumns(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                static::statusFilter(),
                SelectFilter::make('category')->label('Kategori')->options(FacilityCategory::class),
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
            ->emptyStateHeading('Belum ada fasilitas');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
