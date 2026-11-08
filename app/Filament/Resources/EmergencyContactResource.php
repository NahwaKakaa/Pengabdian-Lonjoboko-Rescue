<?php

namespace App\Filament\Resources;

use App\Enums\ContactCategory;
use App\Filament\Resources\EmergencyContactResource\Pages;
use App\Models\EmergencyContact;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmergencyContactResource extends Resource
{
    protected static ?string $model = EmergencyContact::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationGroup = 'Informasi Publik';

    protected static ?string $modelLabel = 'kontak darurat';

    protected static ?string $pluralModelLabel = 'Kontak Darurat';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label('Nama / instansi')
                    ->placeholder('Contoh: Kepala Desa Lonjoboko')
                    ->required()
                    ->maxLength(150)
                    ->columnSpanFull(),
                Select::make('category')
                    ->label('Kelompok')
                    ->options(ContactCategory::class)
                    ->required()
                    ->native(false),
                TextInput::make('phone')
                    ->label('Nomor telepon')
                    ->tel()
                    ->required()
                    ->maxLength(30),
                Toggle::make('is_whatsapp')
                    ->label('Nomor ini bisa dihubungi lewat WhatsApp')
                    ->inline(false),
                Toggle::make('is_active')
                    ->label('Tampilkan di web')
                    ->default(true)
                    ->inline(false),
                TextInput::make('description')
                    ->label('Keterangan')
                    ->placeholder('Contoh: siaga 24 jam')
                    ->maxLength(255)
                    ->columnSpanFull(),
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
                TextColumn::make('name')->label('Nama / instansi')->searchable()->wrap(),
                TextColumn::make('category')->label('Kelompok')->badge(),
                TextColumn::make('phone')->label('Nomor')->copyable()->fontFamily('mono'),
                IconColumn::make('is_whatsapp')->label('WA')->boolean(),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('category')->label('Kelompok')->options(ContactCategory::class),
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
            'index' => Pages\ListEmergencyContacts::route('/'),
            'create' => Pages\CreateEmergencyContact::route('/create'),
            'edit' => Pages\EditEmergencyContact::route('/{record}/edit'),
        ];
    }
}
