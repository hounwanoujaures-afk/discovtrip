<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;
    protected static ?string $modelLabel = 'Pays';
    protected static ?string $pluralModelLabel = 'Pays';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-globe-alt';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Destinations';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du pays')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Code ISO (ex: BJ)')
                            ->required()
                            ->maxLength(3)
                            ->unique(ignoreRecord: true)
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $set('code', strtoupper($state ?? ''))),

                        TextInput::make('currency')
                            ->label('Devise (ex: FCFA)')
                            ->maxLength(10),

                        TextInput::make('phone_code')
                            ->label('Indicatif téléphonique (ex: +229)')
                            ->maxLength(10),
                    ])
                    ->columns(2),

                Section::make('Description & Médias')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        FileUpload::make('flag')
                            ->label('Drapeau')
                            ->image()
                            ->directory('countries/flags')
                            ->disk('public')
                            ->maxSize(512)
                            ->helperText('Format recommandé : PNG 80x60px'),

                        FileUpload::make('cover_image')
                            ->label('Image de couverture')
                            ->image()
                            ->directory('countries/covers')
                            ->disk('public')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9']),
                    ])
                    ->columns(2),

                Section::make('Paramètres')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('flag')
                    ->label('🏴')
                    ->disk('public')
                    ->width(40)
                    ->height(30),

                Tables\Columns\TextColumn::make('name')
                    ->label('Pays')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('currency')
                    ->label('Devise')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cities_count')
                    ->label('Villes')
                    ->counts('cities')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit'   => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}