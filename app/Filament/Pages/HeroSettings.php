<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HeroSettings extends Page implements HasForms
{
    use InteractsWithForms;

    // $view est NON-STATIC dans Filament\Pages\Page — ne pas redeclarer static
    protected string $view = 'filament.pages.hero-settings';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-photo';
    }

    public static function getNavigationLabel(): string
    {
        return 'Images Hero';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Contenu';
    }

    public static function getNavigationSort(): int
    {
        return 10;
    }

    public mixed $hero_home_1       = null;
    public mixed $hero_home_2       = null;
    public mixed $hero_home_3       = null;
    public mixed $hero_offers       = null;
    public mixed $hero_destinations = null;
    public mixed $hero_about        = null;
    public mixed $hero_contact      = null;
    public mixed $hero_blog         = null;

    public function mount(): void
    {
        $keys = [
            'hero_home_1', 'hero_home_2', 'hero_home_3',
            'hero_offers', 'hero_destinations',
            'hero_about', 'hero_contact', 'hero_blog',
        ];

        foreach ($keys as $key) {
            $setting = SiteSetting::where('key', $key)->first();
            if ($setting?->value) {
                $this->$key = [$setting->value];
            }
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('🏠 Page d\'accueil — Slideshow')
                ->description('3 images défilant automatiquement. Format : 1920×1080px.')
                ->icon('heroicon-o-home')
                ->schema([
                    FileUpload::make('hero_home_1')
                        ->label('Image 1 — principale')
                        ->image()->directory('hero')->disk('public')
                        ->maxSize(3072)->imageEditor()
                        ->helperText('Ex : Vue panoramique Cotonou'),

                    FileUpload::make('hero_home_2')
                        ->label('Image 2')
                        ->image()->directory('hero')->disk('public')
                        ->maxSize(3072)->imageEditor()
                        ->helperText('Ex : Route des Esclaves Ouidah'),

                    FileUpload::make('hero_home_3')
                        ->label('Image 3')
                        ->image()->directory('hero')->disk('public')
                        ->maxSize(3072)->imageEditor()
                        ->helperText('Ex : Village lacustre Ganvié'),
                ])
                ->columns(3),

            Section::make('📄 Autres pages')
                ->description('Image de fond des heroes. Format : 1920×600px.')
                ->icon('heroicon-o-rectangle-stack')
                ->schema([
                    FileUpload::make('hero_offers')
                        ->label('Expériences')
                        ->image()->directory('hero')->disk('public')->maxSize(3072)->imageEditor(),

                    FileUpload::make('hero_destinations')
                        ->label('Destinations')
                        ->image()->directory('hero')->disk('public')->maxSize(3072)->imageEditor(),

                    FileUpload::make('hero_about')
                        ->label('À propos')
                        ->image()->directory('hero')->disk('public')->maxSize(3072)->imageEditor(),

                    FileUpload::make('hero_contact')
                        ->label('Contact')
                        ->image()->directory('hero')->disk('public')->maxSize(3072)->imageEditor(),

                    FileUpload::make('hero_blog')
                        ->label('Blog')
                        ->image()->directory('hero')->disk('public')->maxSize(3072)->imageEditor(),
                ])
                ->columns(3),
        ]);
    }

    public function save(): void
    {
        $keys = [
            'hero_home_1', 'hero_home_2', 'hero_home_3',
            'hero_offers', 'hero_destinations',
            'hero_about', 'hero_contact', 'hero_blog',
        ];

        foreach ($keys as $key) {
            $value = $this->$key;
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }
            if ($value) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'type' => 'image', 'description' => "Hero: {$key}"]
                );
            }
        }

        cache()->forget('home.featured_offers');
        cache()->forget('home.stats');

        Notification::make()->title('✅ Images sauvegardées !')->success()->send();
    }
}