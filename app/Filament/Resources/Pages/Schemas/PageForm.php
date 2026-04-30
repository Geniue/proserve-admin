<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Forms\Components\IconPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Homepage Editor')
                    ->schema([
                        Tab::make('English Content')
                            ->schema(self::englishTab()),
                        Tab::make('Arabic Content')
                            ->schema(self::arabicTab()),
                        Tab::make('Settings & SEO')
                            ->schema(self::settingsTab()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function heroiconOptions(): array
    {
        return [
            'academic-cap' => 'Academic Cap',
            'adjustments-horizontal' => 'Adjustments',
            'archive-box' => 'Archive Box',
            'arrow-path' => 'Arrow Path (Refresh)',
            'arrow-trending-up' => 'Trending Up',
            'banknotes' => 'Banknotes',
            'beaker' => 'Beaker',
            'bell' => 'Bell',
            'bolt' => 'Bolt (Lightning)',
            'bookmark' => 'Bookmark',
            'briefcase' => 'Briefcase',
            'bug-ant' => 'Bug / Pest',
            'building-office' => 'Building Office',
            'building-office-2' => 'Building Office 2',
            'building-storefront' => 'Storefront',
            'cake' => 'Cake',
            'calculator' => 'Calculator',
            'calendar' => 'Calendar',
            'camera' => 'Camera',
            'chart-bar' => 'Chart Bar',
            'chat-bubble-left-right' => 'Chat',
            'check-badge' => 'Check Badge',
            'check-circle' => 'Check Circle',
            'clipboard-document-check' => 'Clipboard Check',
            'clock' => 'Clock',
            'cog-6-tooth' => 'Cog (Settings)',
            'computer-desktop' => 'Computer Desktop',
            'cpu-chip' => 'CPU Chip / Electronics',
            'credit-card' => 'Credit Card',
            'cube' => 'Cube',
            'currency-dollar' => 'Dollar',
            'device-phone-mobile' => 'Phone Mobile',
            'document-text' => 'Document',
            'envelope' => 'Envelope',
            'eye' => 'Eye',
            'face-smile' => 'Smile',
            'film' => 'Film',
            'finger-print' => 'Fingerprint',
            'fire' => 'Fire',
            'flag' => 'Flag',
            'gift' => 'Gift',
            'globe-alt' => 'Globe',
            'hand-thumb-up' => 'Thumbs Up',
            'heart' => 'Heart',
            'home' => 'Home',
            'home-modern' => 'Home Modern',
            'identification' => 'ID Card',
            'key' => 'Key',
            'light-bulb' => 'Light Bulb',
            'link' => 'Link',
            'lock-closed' => 'Lock',
            'map-pin' => 'Map Pin',
            'megaphone' => 'Megaphone',
            'paint-brush' => 'Paint Brush',
            'paper-airplane' => 'Paper Airplane',
            'phone' => 'Phone',
            'photo' => 'Photo',
            'puzzle-piece' => 'Puzzle Piece',
            'receipt-percent' => 'Receipt / Discount',
            'rocket-launch' => 'Rocket Launch',
            'scale' => 'Scale',
            'scissors' => 'Scissors',
            'server' => 'Server',
            'shield-check' => 'Shield Check',
            'shopping-bag' => 'Shopping Bag',
            'shopping-cart' => 'Shopping Cart',
            'sparkles' => 'Sparkles (Cleaning)',
            'speaker-wave' => 'Speaker',
            'star' => 'Star',
            'sun' => 'Sun',
            'squares-2x2' => 'Squares Grid',
            'tag' => 'Tag',
            'ticket' => 'Ticket',
            'trophy' => 'Trophy',
            'truck' => 'Truck',
            'tv' => 'TV',
            'user' => 'User',
            'user-group' => 'User Group',
            'users' => 'Users',
            'wifi' => 'WiFi',
            'wrench' => 'Wrench',
            'wrench-screwdriver' => 'Wrench & Screwdriver',
        ];
    }

    private static function imageField(string $statePath, string $label, string $directory = 'homepage'): array
    {
        return [
            FileUpload::make($statePath.'_upload')
                ->label($label.' (Upload)')
                ->helperText('Recommended: 1200 × 800px · Max 2MB · JPG, PNG or WebP')
                ->image()
                ->disk('public')
                ->directory($directory)
                ->maxSize(2048)
                ->imageResizeTargetWidth(1200)
                ->imageResizeTargetHeight(800)
                ->columnSpanFull(),
            TextInput::make($statePath)
                ->label($label.' (URL)')
                ->helperText('Upload an image above or paste an external URL here. Saved automatically on submit.')
                ->columnSpanFull(),
        ];
    }

    private static function logoImageField(string $statePath, string $label): array
    {
        return [
            FileUpload::make($statePath.'_upload')
                ->label($label.' (Upload)')
                ->helperText('Recommended: 640 x 240px or SVG/PNG with transparent background. Max 1MB.')
                ->image()
                ->disk('public')
                ->directory('logo')
                ->maxSize(1024)
                ->imagePreviewHeight('120')
                ->imageResizeMode('contain')
                ->imageResizeTargetWidth('640')
                ->imageResizeTargetHeight('240')
                ->imageResizeUpscale(false)
                ->columnSpanFull(),
            TextInput::make($statePath)
                ->label($label.' (URL)')
                ->helperText('Upload an image above or paste an external URL here.')
                ->columnSpanFull(),
        ];
    }

    private static function englishTab(): array
    {
        return [
            Section::make('Logo')
                ->schema([
                    TextInput::make('content_blocks.logo.text')
                        ->label('Logo Text')
                        ->helperText('Leave empty to show only the logo image')
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Section::make('Dark Logo')
                                ->description('Shown in header on light backgrounds')
                                ->schema([
                                    ...self::logoImageField('content_blocks.logo.dark_image_url', 'Dark Logo'),
                                ]),
                            Section::make('White Logo')
                                ->description('Shown in header on dark mode')
                                ->schema([
                                    ...self::logoImageField('content_blocks.logo.white_image_url', 'White Logo'),
                                ]),
                        ]),
                ])
                ->collapsible(),

            Section::make('Hero Section')
                ->schema([
                    TextInput::make('content_blocks.hero.title.en')
                        ->label('Hero Title')
                        ->required(),
                    Textarea::make('content_blocks.hero.description.en')
                        ->label('Hero Description')
                        ->rows(3),
                    ...self::imageField('content_blocks.hero.image_url', 'Hero Image', 'hero'),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('content_blocks.hero.google_play_url')
                                ->label('Google Play URL')
                                ->url(),
                            Select::make('content_blocks.hero.app_store_badge_mode')
                                ->label('App Store Badge Mode')
                                ->options([
                                    'coming_soon' => 'Coming Soon',
                                    'available' => 'Available',
                                    'hidden' => 'Hidden',
                                ])
                                ->default('coming_soon'),
                        ]),
                    TextInput::make('content_blocks.hero.app_store_badge_label.en')
                        ->label('App Store Badge Label'),
                ])
                ->collapsible(),

            Section::make('About Section')
                ->schema([
                    TextInput::make('content_blocks.about.title.en')
                        ->label('About Title')
                        ->required(),
                    Textarea::make('content_blocks.about.body.en')
                        ->label('About Body')
                        ->rows(4),
                    ...self::imageField('content_blocks.about.image_url', 'About Image', 'about'),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Services Section')
                ->schema([
                    TextInput::make('content_blocks.services.title.en')
                        ->label('Services Title'),
                    TextInput::make('content_blocks.services.description.en')
                        ->label('Services Description'),
                    Repeater::make('content_blocks.services.items')
                        ->label('Services')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    IconPicker::make('icon')
                                        ->label('Icon')
                                        ->options(self::heroiconOptions())
                                        ->required(),
                                    TextInput::make('title.en')
                                        ->label('Title (EN)')
                                        ->required(),
                                ]),
                            TextInput::make('description.en')
                                ->label('Description (EN)'),
                        ])
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Why Choose Us')
                ->schema([
                    TextInput::make('content_blocks.why_choose_us.title.en')
                        ->label('Section Title'),
                    TextInput::make('content_blocks.why_choose_us.description.en')
                        ->label('Section Description'),
                    Repeater::make('content_blocks.why_choose_us.items')
                        ->label('Reasons')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    IconPicker::make('icon')
                                        ->label('Icon')
                                        ->options(self::heroiconOptions())
                                        ->required(),
                                    TextInput::make('title.en')
                                        ->label('Title (EN)')
                                        ->required(),
                                ]),
                            Textarea::make('description.en')
                                ->label('Description (EN)')
                                ->rows(2),
                        ])
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('How It Works')
                ->schema([
                    TextInput::make('content_blocks.how_it_works.title.en')
                        ->label('Section Title'),
                    TextInput::make('content_blocks.how_it_works.description.en')
                        ->label('Section Description'),
                    Repeater::make('content_blocks.how_it_works.steps')
                        ->label('Steps')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('number')
                                        ->label('Step Number'),
                                    TextInput::make('title.en')
                                        ->label('Title (EN)')
                                        ->required(),
                                ]),
                            TextInput::make('description.en')
                                ->label('Description (EN)'),
                        ])
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => ($state['number'] ?? '').' - '.($state['title']['en'] ?? '')),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('CTA Section')
                ->schema([
                    TextInput::make('content_blocks.cta.title.en')
                        ->label('CTA Title'),
                    Textarea::make('content_blocks.cta.description.en')
                        ->label('CTA Description')
                        ->rows(2),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Navigation')
                ->schema([
                    Repeater::make('content_blocks.navigation.items')
                        ->label('Nav Items')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('href')
                                        ->label('Link (#anchor)')
                                        ->required(),
                                    TextInput::make('label.en')
                                        ->label('Label (EN)')
                                        ->required(),
                                ]),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label']['en'] ?? null),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Footer')
                ->schema([
                    Section::make('Footer Logo')
                        ->description('Logo displayed in the footer (dark background). Falls back to white logo if not set.')
                        ->schema([
                            ...self::logoImageField('content_blocks.footer.logo_image_url', 'Footer Logo'),
                        ])
                        ->collapsible()
                        ->collapsed(),
                    Textarea::make('content_blocks.footer.brand_blurb.en')
                        ->label('Brand Blurb')
                        ->rows(2),
                    TextInput::make('content_blocks.footer.contact_title.en')
                        ->label('Contact Title'),
                    TextInput::make('content_blocks.footer.quick_links_title.en')
                        ->label('Quick Links Title'),
                    TextInput::make('content_blocks.footer.social_title.en')
                        ->label('Social Title'),
                    TextInput::make('content_blocks.footer.copyright.en')
                        ->label('Copyright Text'),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('content_blocks.footer.contact.phone')
                                ->label('Phone'),
                            TextInput::make('content_blocks.footer.contact.email')
                                ->label('Email')
                                ->email(),
                            TextInput::make('content_blocks.footer.contact.address.en')
                                ->label('Address (EN)'),
                        ]),
                ])
                ->collapsible()
                ->collapsed(),
        ];
    }

    private static function arabicTab(): array
    {
        return [
            Section::make('Hero Section (Arabic)')
                ->schema([
                    TextInput::make('content_blocks.hero.title.ar')
                        ->label('Hero Title (AR)')
                        ->required(),
                    Textarea::make('content_blocks.hero.description.ar')
                        ->label('Hero Description (AR)')
                        ->rows(3),
                    TextInput::make('content_blocks.hero.app_store_badge_label.ar')
                        ->label('App Store Badge Label (AR)'),
                ])
                ->collapsible(),

            Section::make('About Section (Arabic)')
                ->schema([
                    TextInput::make('content_blocks.about.title.ar')
                        ->label('About Title (AR)')
                        ->required(),
                    Textarea::make('content_blocks.about.body.ar')
                        ->label('About Body (AR)')
                        ->rows(4),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Services Section (Arabic)')
                ->schema([
                    TextInput::make('content_blocks.services.title.ar')
                        ->label('Services Title (AR)'),
                    TextInput::make('content_blocks.services.description.ar')
                        ->label('Services Description (AR)'),
                    Repeater::make('content_blocks.services.items')
                        ->label('Services')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    IconPicker::make('icon')
                                        ->label('Icon')
                                        ->options(self::heroiconOptions())
                                        ->disabled(),
                                    TextInput::make('title.ar')
                                        ->label('Title (AR)')
                                        ->required(),
                                ]),
                            TextInput::make('description.ar')
                                ->label('Description (AR)'),
                        ])
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => $state['title']['ar'] ?? $state['title']['en'] ?? null),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Why Choose Us (Arabic)')
                ->schema([
                    TextInput::make('content_blocks.why_choose_us.title.ar')
                        ->label('Section Title (AR)'),
                    TextInput::make('content_blocks.why_choose_us.description.ar')
                        ->label('Section Description (AR)'),
                    Repeater::make('content_blocks.why_choose_us.items')
                        ->label('Reasons')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    IconPicker::make('icon')
                                        ->label('Icon')
                                        ->options(self::heroiconOptions())
                                        ->disabled(),
                                    TextInput::make('title.ar')
                                        ->label('Title (AR)')
                                        ->required(),
                                ]),
                            Textarea::make('description.ar')
                                ->label('Description (AR)')
                                ->rows(2),
                        ])
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => $state['title']['ar'] ?? null),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('How It Works (Arabic)')
                ->schema([
                    TextInput::make('content_blocks.how_it_works.title.ar')
                        ->label('Section Title (AR)'),
                    TextInput::make('content_blocks.how_it_works.description.ar')
                        ->label('Section Description (AR)'),
                    Repeater::make('content_blocks.how_it_works.steps')
                        ->label('Steps')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('number')
                                        ->label('Step Number')
                                        ->disabled(),
                                    TextInput::make('title.ar')
                                        ->label('Title (AR)')
                                        ->required(),
                                ]),
                            TextInput::make('description.ar')
                                ->label('Description (AR)'),
                        ])
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => ($state['number'] ?? '').' - '.($state['title']['ar'] ?? '')),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('CTA Section (Arabic)')
                ->schema([
                    TextInput::make('content_blocks.cta.title.ar')
                        ->label('CTA Title (AR)'),
                    Textarea::make('content_blocks.cta.description.ar')
                        ->label('CTA Description (AR)')
                        ->rows(2),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Navigation (Arabic)')
                ->schema([
                    Repeater::make('content_blocks.navigation.items')
                        ->label('Nav Items')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('href')
                                        ->label('Link')
                                        ->disabled(),
                                    TextInput::make('label.ar')
                                        ->label('Label (AR)')
                                        ->required(),
                                ]),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label']['ar'] ?? null),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Footer (Arabic)')
                ->schema([
                    Textarea::make('content_blocks.footer.brand_blurb.ar')
                        ->label('Brand Blurb (AR)')
                        ->rows(2),
                    TextInput::make('content_blocks.footer.contact_title.ar')
                        ->label('Contact Title (AR)'),
                    TextInput::make('content_blocks.footer.quick_links_title.ar')
                        ->label('Quick Links Title (AR)'),
                    TextInput::make('content_blocks.footer.social_title.ar')
                        ->label('Social Title (AR)'),
                    TextInput::make('content_blocks.footer.copyright.ar')
                        ->label('Copyright Text (AR)'),
                    TextInput::make('content_blocks.footer.contact.address.ar')
                        ->label('Address (AR)'),
                ])
                ->collapsible()
                ->collapsed(),
        ];
    }

    private static function settingsTab(): array
    {
        return [
            Section::make('Page Settings')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('title')
                                ->label('Internal Title')
                                ->required(),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->default('home')
                                ->disabled()
                                ->dehydrated(),
                        ]),
                    Toggle::make('is_active')
                        ->label('Page Active')
                        ->default(true),
                ]),

            Section::make('SEO - English')
                ->schema([
                    TextInput::make('seo_translations.en.title')
                        ->label('SEO Title (EN)')
                        ->maxLength(70),
                    Textarea::make('seo_translations.en.meta_description')
                        ->label('Meta Description (EN)')
                        ->maxLength(160)
                        ->rows(2),
                    TextInput::make('seo_translations.en.og_title')
                        ->label('OG Title (EN)'),
                    TextInput::make('seo_translations.en.og_description')
                        ->label('OG Description (EN)'),
                ])
                ->collapsible(),

            Section::make('SEO - Arabic')
                ->schema([
                    TextInput::make('seo_translations.ar.title')
                        ->label('SEO Title (AR)')
                        ->maxLength(70),
                    Textarea::make('seo_translations.ar.meta_description')
                        ->label('Meta Description (AR)')
                        ->maxLength(160)
                        ->rows(2),
                    TextInput::make('seo_translations.ar.og_title')
                        ->label('OG Title (AR)'),
                    TextInput::make('seo_translations.ar.og_description')
                        ->label('OG Description (AR)'),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Schema Markup')
                ->schema([
                    Textarea::make('schema_markup_json')
                        ->label('Schema.org JSON-LD')
                        ->rows(6)
                        ->helperText('Paste valid JSON-LD markup'),
                ])
                ->collapsible()
                ->collapsed(),
        ];
    }
}
