<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $modelLabel = 'Avis';
    protected static ?string $pluralModelLabel = 'Avis clients';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-star';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Contenu';
    }

    public static function getNavigationSort(): int
    {
        return 20;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Avis')
                ->schema([
                    Select::make('rating')
                        ->label('Note')
                        ->options([1 => '1 étoile', 2 => '2 étoiles', 3 => '3 étoiles', 4 => '4 étoiles', 5 => '5 étoiles'])
                        ->required(),

                    Textarea::make('comment')
                        ->label('Commentaire')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),

                    Select::make('status')
                        ->label('Statut')
                        ->options([
                            'pending'   => 'En attente',
                            'published' => 'Publié',
                            'rejected'  => 'Rejeté',
                            'spam'      => 'Spam',
                        ])
                        ->required(),

                    Textarea::make('moderator_notes')
                        ->label('Notes de modération (interne)')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('offer.title')
                    ->label('Offre')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Note')
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', (int) $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Commentaire')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'pending'   => 'warning',
                        'rejected'  => 'danger',
                        'spam'      => 'gray',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending'   => 'En attente',
                        'published' => 'Publié',
                        'rejected'  => 'Rejeté',
                        'spam'      => 'Spam',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Review $record) => $record->status !== 'published')
                    ->action(function (Review $record) {
                        $record->update(['status' => 'published', 'published_at' => now()]);
                        Notification::make()->title('Avis publié')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Review $record) => $record->status !== 'rejected')
                    ->action(function (Review $record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Avis rejeté')->danger()->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit'  => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
