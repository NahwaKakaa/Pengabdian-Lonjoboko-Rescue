<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VolunteerResource\Pages;
use App\Models\Volunteer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VolunteerResource extends Resource
{
    protected static ?string $model = Volunteer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Informasi Publik';

    protected static ?string $modelLabel = 'relawan';

    protected static ?string $pluralModelLabel = 'Kader & Relawan';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(150),
                TextInput::make('position')
                    ->label('Peran')
                    ->placeholder('Contoh: Koordinator, Tim Evakuasi, Tim P3K')
                    ->maxLength(100),
                TextInput::make('hamlet')
                    ->label('Dusun')
                    ->maxLength(100),
                TextInput::make('phone')
                    ->label('Nomor HP')
                    ->tel()
                    ->maxLength(30),
                Toggle::make('show_phone')
                    ->label('Tampilkan nomor HP di web')
                    ->helperText('Hanya aktifkan bila yang bersangkutan setuju.')
                    ->inline(false),
                Toggle::make('is_active')
                    ->label('Tampilkan di web')
                    ->default(true)
                    ->inline(false),
                FileUpload::make('photo')
                    ->label('Foto')
                    ->image()
                    ->avatar()
                    ->directory('relawan')
                    ->maxSize(2048),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')->label('')->circular()->defaultImageUrl(null),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('position')->label('Peran')->placeholder('-'),
                TextColumn::make('hamlet')->label('Dusun')->placeholder('-'),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => Pages\ListVolunteers::route('/'),
            'create' => Pages\CreateVolunteer::route('/create'),
            'edit' => Pages\EditVolunteer::route('/{record}/edit'),
        ];
    }
}
