<?php

namespace App\Filament\Resources;

use App\Enums\ContactCategory;
use App\Filament\Resources\EmergencyContactResource\Pages;
use App\Models\EmergencyContact;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
            ->schema([
                Section::make('Data kontak')
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
                            ->default(ContactCategory::Desa->value)
                            ->required()
                            ->native(false),
                        TextInput::make('phone')
                            ->label('Nomor telepon')
                            ->tel()
                            ->placeholder('0812xxxxxxxx atau 112')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('description')
                            ->label('Keterangan')
                            ->placeholder('Contoh: siaga 24 jam')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Tampil di web')
                    ->description('Atur di mana kontak ini muncul untuk warga.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_whatsapp')
                            ->label('Bisa dihubungi lewat WhatsApp')
                            ->helperText('Muncul tombol WA di samping nomor.'),
                        Toggle::make('is_active')
                            ->label('Tampilkan di halaman Info Darurat')
                            ->default(true)
                            ->live(),
                        Toggle::make('show_in_footer')
                            ->label('Tampilkan juga di footer web')
                            ->helperText('Bagian bawah setiap halaman. Maksimal '.EmergencyContact::FOOTER_LIMIT.' kontak teratas yang muat.')
                            ->default(true)
                            ->disabled(fn (Get $get) => ! $get('is_active')),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->helperText('Angka kecil tampil lebih atas. Bisa juga diseret di tabel.')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn () => (int) EmergencyContact::max('sort_order') + 1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Tekan tombol "Ubah urutan" lalu seret baris untuk mengatur urutan. Kontak yang dicentang "Footer" tampil di bagian bawah web (maks. '.EmergencyContact::FOOTER_LIMIT.' teratas).')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama / instansi')
                    ->searchable()
                    ->wrap()
                    ->description(fn (EmergencyContact $record) => $record->description),
                TextColumn::make('category')->label('Kelompok')->badge(),
                TextColumn::make('phone')
                    ->label('Nomor')
                    ->copyable()
                    ->copyMessage('Nomor disalin')
                    ->weight('bold')
                    ->fontFamily('mono'),
                ToggleColumn::make('is_whatsapp')->label('WA'),
                ToggleColumn::make('is_active')->label('Info Darurat'),
                ToggleColumn::make('show_in_footer')
                    ->label('Footer')
                    ->disabled(fn (EmergencyContact $record) => ! $record->is_active),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('category')->label('Kelompok')->options(ContactCategory::class),
                TernaryFilter::make('show_in_footer')->label('Tampil di footer'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada kontak darurat')
            ->emptyStateDescription('Tambahkan nomor kepala desa, BPBD Gowa, puskesmas, atau posko kader.');
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
