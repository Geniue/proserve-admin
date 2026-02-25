<?php

namespace App\Filament\Resources\ThemeConfigs\Schemas;

use App\Models\ThemeConfig;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ThemeConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Theme Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Theme Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name_ar')
                                    ->label('Arabic Name')
                                    ->maxLength(255),
                                TextInput::make('firebase_id')
                                    ->label('Firebase ID')
                                    ->default('primary_theme_v1')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Unique identifier for Firestore'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('brightness')
                                    ->options([
                                        'light' => 'Light Mode',
                                        'dark' => 'Dark Mode',
                                    ])
                                    ->default('light')
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Active Theme')
                                    ->default(true)
                                    ->helperText('Only one theme can be active'),
                                Placeholder::make('version')
                                    ->label('Version')
                                    ->content(fn ($record) => $record?->version ?? 1),
                            ]),
                    ])
                    ->collapsible(),

                Tabs::make('Colors')
                    ->tabs([
                        Tab::make('Primary')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Grid::make(3)->schema(self::buildColorFields([
                                    'primary_dark_blue' => 'Primary Dark Blue',
                                    'primary_teal' => 'Primary Teal',
                                    'secondary_teal' => 'Secondary Teal',
                                ])),
                            ]),

                        Tab::make('Neutral')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Grid::make(3)->schema(self::buildColorFields([
                                    'gray_light' => 'Gray Light',
                                    'gray_medium' => 'Gray Medium',
                                    'gray_dark' => 'Gray Dark',
                                ])),
                            ]),

                        Tab::make('Text')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(3)->schema(self::buildColorFields([
                                    'text_dark' => 'Text Dark',
                                    'text_light' => 'Text Light',
                                    'text_muted' => 'Text Muted',
                                ])),
                            ]),

                        Tab::make('Semantic')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                Grid::make(4)->schema(self::buildColorFields([
                                    'color_error' => 'Error',
                                    'color_success' => 'Success',
                                    'color_warning' => 'Warning',
                                    'color_info' => 'Info',
                                ])),
                            ]),

                        Tab::make('Surface')
                            ->icon('heroicon-o-square-3-stack-3d')
                            ->schema([
                                Grid::make(4)->schema(self::buildColorFields([
                                    'color_surface' => 'Surface',
                                    'color_background' => 'Background',
                                    'color_card' => 'Card',
                                    'color_divider' => 'Divider',
                                ])),
                            ]),

                        Tab::make('Status')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Grid::make(3)->schema(self::buildColorFields([
                                    'status_pending' => 'Pending',
                                    'status_confirmed' => 'Confirmed',
                                    'status_in_progress' => 'In Progress',
                                    'status_completed' => 'Completed',
                                    'status_cancelled' => 'Cancelled',
                                    'status_default' => 'Default',
                                ])),
                            ]),

                        Tab::make('Buttons')
                            ->icon('heroicon-o-cursor-arrow-rays')
                            ->schema([
                                Grid::make(3)->schema(self::buildColorFields([
                                    'button_primary' => 'Primary Button',
                                    'button_secondary' => 'Secondary Button',
                                    'button_danger' => 'Danger Button',
                                    'button_success' => 'Success Button',
                                    'button_text' => 'Button Text',
                                    'button_text_dark' => 'Button Text Dark',
                                ])),
                            ]),

                        Tab::make('Chat')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Grid::make(4)->schema(self::buildColorFields([
                                    'chat_bubble_me' => 'My Bubble',
                                    'chat_bubble_other' => 'Other Bubble',
                                    'chat_background' => 'Chat Background',
                                    'chat_icon' => 'Chat Icon',
                                ])),
                            ]),

                        Tab::make('Rating & Nav')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Grid::make(4)->schema(self::buildColorFields([
                                    'rating_active' => 'Rating Active',
                                    'rating_inactive' => 'Rating Inactive',
                                    'nav_active' => 'Nav Active',
                                    'nav_inactive' => 'Nav Inactive',
                                ])),
                            ]),

                        Tab::make('Accent')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Grid::make(3)->schema(self::buildColorFields([
                                    'accent_teal' => 'Teal',
                                    'accent_orange' => 'Orange',
                                    'accent_red' => 'Red',
                                    'accent_green' => 'Green',
                                    'accent_blue' => 'Blue',
                                    'accent_amber' => 'Amber',
                                ])),
                            ]),

                        Tab::make('Input & Slider')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Grid::make(3)->schema(self::buildColorFields([
                                    'input_fill' => 'Input Fill',
                                    'input_border' => 'Input Border',
                                    'input_hint' => 'Input Hint',
                                    'slider_background' => 'Slider Background',
                                    'slider_dot_active' => 'Slider Active Dot',
                                    'slider_dot_inactive' => 'Slider Inactive Dot',
                                ])),
                            ]),

                        Tab::make('Utility')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Grid::make(2)->schema(self::buildColorFields([
                                    'color_shadow' => 'Shadow',
                                    'color_overlay' => 'Overlay',
                                ])),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Sync Information')
                    ->schema([
                        Placeholder::make('last_synced_at')
                            ->label('Last Synced')
                            ->content(fn ($record) => $record?->last_synced_at?->diffForHumans() ?? 'Never'),
                        Placeholder::make('updated_by_name')
                            ->label('Last Updated By')
                            ->content(fn ($record) => $record?->updatedByUser?->name ?? 'Unknown'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->hiddenOn('create'),
            ]);
    }

    /**
     * Build color picker fields - converts between hex display and integer storage
     */
    protected static function buildColorFields(array $fields): array
    {
        $components = [];

        foreach ($fields as $dbField => $label) {
            $components[] = ColorPicker::make($dbField)
                ->label($label)
                ->required()
                ->formatStateUsing(function ($state) use ($dbField) {
                    // Convert integer to hex for display (6-char RGB without alpha)
                    if (is_numeric($state) && $state > 0) {
                        // Convert int to full 8-char hex, then take only RGB part (last 6 chars)
                        $fullHex = str_pad(dechex((int) $state), 8, '0', STR_PAD_LEFT);
                        return '#' . strtoupper(substr($fullHex, 2)); // Skip alpha, return #RRGGBB
                    }
                    // If already a hex string, normalize it
                    if (is_string($state) && str_starts_with($state, '#')) {
                        $hex = ltrim($state, '#');
                        if (strlen($hex) === 8) {
                            return '#' . strtoupper(substr($hex, 2)); // Strip alpha
                        }
                        return '#' . strtoupper($hex);
                    }
                    return '#000000';
                })
                ->dehydrateStateUsing(function ($state) use ($dbField) {
                    // Convert hex to integer for storage (add FF alpha)
                    if (empty($state)) {
                        return 0xFF000000; // Default to black with full alpha
                    }
                    if (is_string($state)) {
                        $hex = ltrim($state, '#');
                        // If 6 chars (RGB), add FF alpha prefix
                        if (strlen($hex) === 6) {
                            $hex = 'FF' . $hex;
                        }
                        // If 8 chars (ARGB), use as-is
                        if (strlen($hex) === 8) {
                            return (int) hexdec($hex);
                        }
                    }
                    // If already numeric, return as-is
                    if (is_numeric($state)) {
                        return (int) $state;
                    }
                    return 0xFF000000;
                });
        }

        return $components;
    }

    /**
     * Get default color value for a field
     */
    protected static function getDefaultColorValue(string $field): int
    {
        $defaults = ThemeConfig::getPresetThemes()['proserve_default']['colors'] ?? [];
        return $defaults[$field] ?? 0xFF000000;
    }
}
