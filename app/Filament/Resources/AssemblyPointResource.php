<?php

namespace App\Filament\Resources;

use App\Enums\AssemblyPointType;
use App\Filament\Forms\Components\LocationPicker;
use App\Filament\Resources\AssemblyPointResource\Pages;
use App\Filament\Resources\Concerns\HasVerificationWorkflow;
use App\Models\AssemblyPoint;
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

class AssemblyPointResource extends Resource
{
    use HasVerificationWorkflow;

    protected static ?string $model = AssemblyPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Peta & Evakuasi';

    protected static ?string $modelLabel = 'titik kumpul';

    protected static ?string $pluralModelLabel = 'Titik Kumpul & Pengungsian';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Keterangan')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama tempat')
                        ->placeholder('Contoh: Lapangan Desa Lonjoboko')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),
                    Select::make('type')
                        ->label('Jenis')
                        ->options(AssemblyPointType::class)
                        ->default(AssemblyPointType::TitikKumpul->value)
                        ->required()
                        ->native(false),
                    TextInput::make('capacity')
                        ->label('Perkiraan daya tampung')
                        ->numeric()
                        ->minValue(1)
                        ->suffix('orang'),
                    TextInput::make('address')
                        ->label('Alamat / patokan')
                        ->placeholder('Contoh: depan masjid Dusun ...')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('facilities')
                        ->label('Fasilitas yang tersedia')
                        ->placeholder('Contoh: toilet, air bersih, tenda, genset')
                        ->rows(2)
                        ->columnSpanFull(),
                    FileUpload::make('photo')
                        ->label('Foto tempat')
                        ->image()
                        ->directory('titik-kumpul')
                        ->maxSize(4096)
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
                TextColumn::make('name')->label('Nama tempat')->searchable()->wrap(),
                TextColumn::make('type')->label('Jenis')->badge(),
                TextColumn::make('capacity')->label('Daya tampung')->numeric()->suffix(' orang')->placeholder('-')->sortable(),
                ...static::verificationColumns(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                static::statusFilter(),
                SelectFilter::make('type')->label('Jenis')->options(AssemblyPointType::class),
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
            ->emptyStateHeading('Belum ada titik kumpul');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssemblyPoints::route('/'),
            'create' => Pages\CreateAssemblyPoint::route('/create'),
            'edit' => Pages\EditAssemblyPoint::route('/{record}/edit'),
        ];
    }
}
