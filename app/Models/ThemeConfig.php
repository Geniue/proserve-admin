<?php

namespace App\Models;

use App\Traits\SyncToFirestore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeConfig extends Model
{
    use HasFactory, SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'name',
        'name_ar',
        'version',
        'is_active',
        'brightness',
        // Primary Colors
        'primary_dark_blue',
        'primary_teal',
        'secondary_teal',
        // Neutral Colors
        'gray_light',
        'gray_medium',
        'gray_dark',
        // Text Colors
        'text_dark',
        'text_light',
        'text_muted',
        // Semantic Colors
        'color_error',
        'color_success',
        'color_warning',
        'color_info',
        // Surface Colors
        'color_surface',
        'color_background',
        'color_card',
        'color_divider',
        // Status Colors
        'status_pending',
        'status_confirmed',
        'status_in_progress',
        'status_completed',
        'status_cancelled',
        'status_default',
        // Button Colors
        'button_primary',
        'button_secondary',
        'button_danger',
        'button_success',
        'button_text',
        'button_text_dark',
        // Chat Colors
        'chat_bubble_me',
        'chat_bubble_other',
        'chat_background',
        'chat_icon',
        // Rating Colors
        'rating_active',
        'rating_inactive',
        // Navigation Colors
        'nav_active',
        'nav_inactive',
        // Accent Colors
        'accent_teal',
        'accent_orange',
        'accent_red',
        'accent_green',
        'accent_blue',
        'accent_amber',
        // Input Colors
        'input_fill',
        'input_border',
        'input_hint',
        // Slider Colors
        'slider_background',
        'slider_dot_active',
        'slider_dot_inactive',
        // Utility Colors
        'color_shadow',
        'color_overlay',
        // Misc
        'theme_data',
        'last_synced_at',
        'updated_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
        'theme_data' => 'array',
        'last_synced_at' => 'datetime',
        // All color fields are stored as integers (Flutter Color.value)
        'primary_dark_blue' => 'integer',
        'primary_teal' => 'integer',
        'secondary_teal' => 'integer',
        'gray_light' => 'integer',
        'gray_medium' => 'integer',
        'gray_dark' => 'integer',
        'text_dark' => 'integer',
        'text_light' => 'integer',
        'text_muted' => 'integer',
        'color_error' => 'integer',
        'color_success' => 'integer',
        'color_warning' => 'integer',
        'color_info' => 'integer',
        'color_surface' => 'integer',
        'color_background' => 'integer',
        'color_card' => 'integer',
        'color_divider' => 'integer',
        'status_pending' => 'integer',
        'status_confirmed' => 'integer',
        'status_in_progress' => 'integer',
        'status_completed' => 'integer',
        'status_cancelled' => 'integer',
        'status_default' => 'integer',
        'button_primary' => 'integer',
        'button_secondary' => 'integer',
        'button_danger' => 'integer',
        'button_success' => 'integer',
        'button_text' => 'integer',
        'button_text_dark' => 'integer',
        'chat_bubble_me' => 'integer',
        'chat_bubble_other' => 'integer',
        'chat_background' => 'integer',
        'chat_icon' => 'integer',
        'rating_active' => 'integer',
        'rating_inactive' => 'integer',
        'nav_active' => 'integer',
        'nav_inactive' => 'integer',
        'accent_teal' => 'integer',
        'accent_orange' => 'integer',
        'accent_red' => 'integer',
        'accent_green' => 'integer',
        'accent_blue' => 'integer',
        'accent_amber' => 'integer',
        'input_fill' => 'integer',
        'input_border' => 'integer',
        'input_hint' => 'integer',
        'slider_background' => 'integer',
        'slider_dot_active' => 'integer',
        'slider_dot_inactive' => 'integer',
        'color_shadow' => 'integer',
        'color_overlay' => 'integer',
    ];

    /**
     * Map database column names to Firestore field names (camelCase for Flutter)
     */
    public static array $colorFieldMap = [
        // Primary
        'primary_dark_blue' => 'primaryDarkBlue',
        'primary_teal' => 'primaryTeal',
        'secondary_teal' => 'secondaryTeal',
        // Neutral
        'gray_light' => 'grayLight',
        'gray_medium' => 'grayMedium',
        'gray_dark' => 'grayDark',
        // Text
        'text_dark' => 'textDark',
        'text_light' => 'textLight',
        'text_muted' => 'textMuted',
        // Semantic
        'color_error' => 'error',
        'color_success' => 'success',
        'color_warning' => 'warning',
        'color_info' => 'info',
        // Surface
        'color_surface' => 'surface',
        'color_background' => 'background',
        'color_card' => 'card',
        'color_divider' => 'divider',
        // Status
        'status_pending' => 'statusPending',
        'status_confirmed' => 'statusConfirmed',
        'status_in_progress' => 'statusInProgress',
        'status_completed' => 'statusCompleted',
        'status_cancelled' => 'statusCancelled',
        'status_default' => 'statusDefault',
        // Buttons
        'button_primary' => 'buttonPrimary',
        'button_secondary' => 'buttonSecondary',
        'button_danger' => 'buttonDanger',
        'button_success' => 'buttonSuccess',
        'button_text' => 'buttonText',
        'button_text_dark' => 'buttonTextDark',
        // Chat
        'chat_bubble_me' => 'chatBubbleMe',
        'chat_bubble_other' => 'chatBubbleOther',
        'chat_background' => 'chatBackground',
        'chat_icon' => 'chatIcon',
        // Rating
        'rating_active' => 'ratingActive',
        'rating_inactive' => 'ratingInactive',
        // Navigation
        'nav_active' => 'navActive',
        'nav_inactive' => 'navInactive',
        // Accent
        'accent_teal' => 'accentTeal',
        'accent_orange' => 'accentOrange',
        'accent_red' => 'accentRed',
        'accent_green' => 'accentGreen',
        'accent_blue' => 'accentBlue',
        'accent_amber' => 'accentAmber',
        // Input
        'input_fill' => 'inputFill',
        'input_border' => 'inputBorder',
        'input_hint' => 'inputHint',
        // Slider
        'slider_background' => 'sliderBackground',
        'slider_dot_active' => 'sliderDotActive',
        'slider_dot_inactive' => 'sliderDotInactive',
        // Utility
        'color_shadow' => 'shadow',
        'color_overlay' => 'overlay',
    ];

    /**
     * Get all color fields as array
     */
    public static function getColorFields(): array
    {
        return array_keys(self::$colorFieldMap);
    }

    /**
     * Relationships
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ThemeChangeLog::class);
    }

    /**
     * Get the active theme
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * SyncToFirestore trait implementation
     */
    public function getFirestoreCollection(): string
    {
        return 'theme_configs';
    }

    public function getFirestoreDocumentId(): string
    {
        return $this->firebase_id ?? 'theme';
    }

    /**
     * Convert model to Firestore format
     * Matches the Flutter app's expected structure with nested 'colors' map
     */
    public function toFirestoreArray(): array
    {
        // Build the colors map (nested under 'colors' key for Flutter app)
        $colors = [];
        foreach (self::$colorFieldMap as $dbColumn => $firestoreKey) {
            $colors[$firestoreKey] = (int) $this->$dbColumn;
        }

        $data = [
            'id' => $this->firebase_id,
            'name' => $this->name,
            'nameAr' => $this->name_ar,
            'version' => (int) $this->version,
            'isActive' => (bool) $this->is_active,
            'brightness' => $this->brightness,
            // Nested colors map for Flutter app
            'colors' => $colors,
        ];

        // Also add some top-level color fields that Flutter might read directly
        // (based on your Firestore structure showing both nested and top-level)
        $topLevelColors = [
            'primaryTeal', 'secondaryTeal', 'accentTeal',
            'buttonPrimary', 'buttonSecondary',
            'navActive', 'navInactive',
            'sliderActive', 'sliderInactive',
        ];
        foreach ($topLevelColors as $colorKey) {
            $dbKey = array_search($colorKey, self::$colorFieldMap);
            if ($dbKey && isset($this->$dbKey)) {
                $data[$colorKey] = (int) $this->$dbKey;
            }
        }

        // Add theme_data if present
        if ($this->theme_data) {
            $data['themeData'] = $this->theme_data;
        }

        return $data;
    }

    /**
     * Convert hex color string to Flutter Color.value integer
     * Example: '#FF43A196' or '43A196' => 4282499478
     */
    public static function hexToInt(string $hex): int
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Add FF alpha if only 6 characters (RGB)
        if (strlen($hex) === 6) {
            $hex = 'FF' . $hex;
        }

        // Ensure 8 characters (ARGB)
        if (strlen($hex) !== 8) {
            throw new \InvalidArgumentException("Invalid hex color: {$hex}");
        }

        return hexdec($hex);
    }

    /**
     * Convert Flutter Color.value integer to hex string
     * Example: 4282499478 => '#FF43A196'
     */
    public static function intToHex(int $value): string
    {
        return '#' . strtoupper(str_pad(dechex($value), 8, '0', STR_PAD_LEFT));
    }

    /**
     * Get a color value as hex string
     */
    public function getColorAsHex(string $field): string
    {
        $value = $this->$field ?? 0;
        return self::intToHex($value);
    }

    /**
     * Set a color value from hex string
     */
    public function setColorFromHex(string $field, string $hex): void
    {
        $this->$field = self::hexToInt($hex);
    }

    /**
     * Get all colors as hex array (for admin display)
     */
    public function getColorsAsHex(): array
    {
        $colors = [];
        foreach (self::$colorFieldMap as $dbColumn => $firestoreKey) {
            $colors[$dbColumn] = $this->getColorAsHex($dbColumn);
        }
        return $colors;
    }

    /**
     * Get colors grouped by category
     */
    public static function getColorGroups(): array
    {
        return [
            'Primary' => [
                'primary_dark_blue' => 'Primary Dark Blue',
                'primary_teal' => 'Primary Teal',
                'secondary_teal' => 'Secondary Teal',
            ],
            'Neutral' => [
                'gray_light' => 'Gray Light',
                'gray_medium' => 'Gray Medium',
                'gray_dark' => 'Gray Dark',
            ],
            'Text' => [
                'text_dark' => 'Text Dark',
                'text_light' => 'Text Light',
                'text_muted' => 'Text Muted',
            ],
            'Semantic' => [
                'color_error' => 'Error',
                'color_success' => 'Success',
                'color_warning' => 'Warning',
                'color_info' => 'Info',
            ],
            'Surface' => [
                'color_surface' => 'Surface',
                'color_background' => 'Background',
                'color_card' => 'Card',
                'color_divider' => 'Divider',
            ],
            'Status' => [
                'status_pending' => 'Pending',
                'status_confirmed' => 'Confirmed',
                'status_in_progress' => 'In Progress',
                'status_completed' => 'Completed',
                'status_cancelled' => 'Cancelled',
                'status_default' => 'Default',
            ],
            'Buttons' => [
                'button_primary' => 'Primary Button',
                'button_secondary' => 'Secondary Button',
                'button_danger' => 'Danger Button',
                'button_success' => 'Success Button',
                'button_text' => 'Button Text',
                'button_text_dark' => 'Button Text Dark',
            ],
            'Chat' => [
                'chat_bubble_me' => 'My Bubble',
                'chat_bubble_other' => 'Other Bubble',
                'chat_background' => 'Chat Background',
                'chat_icon' => 'Chat Icon',
            ],
            'Rating' => [
                'rating_active' => 'Active Star',
                'rating_inactive' => 'Inactive Star',
            ],
            'Navigation' => [
                'nav_active' => 'Active Nav',
                'nav_inactive' => 'Inactive Nav',
            ],
            'Accent' => [
                'accent_teal' => 'Teal',
                'accent_orange' => 'Orange',
                'accent_red' => 'Red',
                'accent_green' => 'Green',
                'accent_blue' => 'Blue',
                'accent_amber' => 'Amber',
            ],
            'Input' => [
                'input_fill' => 'Fill',
                'input_border' => 'Border',
                'input_hint' => 'Hint Text',
            ],
            'Slider' => [
                'slider_background' => 'Background',
                'slider_dot_active' => 'Active Dot',
                'slider_dot_inactive' => 'Inactive Dot',
            ],
            'Utility' => [
                'color_shadow' => 'Shadow',
                'color_overlay' => 'Overlay',
            ],
        ];
    }

    /**
     * Preset theme configurations
     */
    public static function getPresetThemes(): array
    {
        return [
            'proserve_default' => [
                'name' => 'ProServe Default',
                'colors' => [
                    'primary_dark_blue' => 0xFF040C1E,
                    'primary_teal' => 0xFF43A196,
                    'secondary_teal' => 0xFF4CB7B0,
                    'gray_light' => 0xFFF5F5F5,
                    'gray_medium' => 0xFFE0E0E0,
                    'gray_dark' => 0xFF757575,
                    'text_dark' => 0xFF212121,
                    'text_light' => 0xFFFFFFFF,
                    'text_muted' => 0xFF9E9E9E,
                    'color_error' => 0xFFE53935,
                    'color_success' => 0xFF43A047,
                    'color_warning' => 0xFFFFA000,
                    'color_info' => 0xFF1E88E5,
                    'color_surface' => 0xFFFFFFFF,
                    'color_background' => 0xFFF5F5F5,
                    'color_card' => 0xFFFFFFFF,
                    'color_divider' => 0xFFBDBDBD,
                    'status_pending' => 0xFFFF9800,
                    'status_confirmed' => 0xFF2196F3,
                    'status_in_progress' => 0xFFFFC107,
                    'status_completed' => 0xFF4CAF50,
                    'status_cancelled' => 0xFFF44336,
                    'status_default' => 0xFF9E9E9E,
                    'button_primary' => 0xFF43A196,
                    'button_secondary' => 0xFF757575,
                    'button_danger' => 0xFFE53935,
                    'button_success' => 0xFF43A047,
                    'button_text' => 0xFFFFFFFF,
                    'button_text_dark' => 0xFF212121,
                    'chat_bubble_me' => 0xFF43A196,
                    'chat_bubble_other' => 0xFFE0E0E0,
                    'chat_background' => 0xFFF5F5F5,
                    'chat_icon' => 0xFF43A196,
                    'rating_active' => 0xFFFFC107,
                    'rating_inactive' => 0xFFE0E0E0,
                    'nav_active' => 0xFF43A196,
                    'nav_inactive' => 0xFF9E9E9E,
                    'accent_teal' => 0xFF009688,
                    'accent_orange' => 0xFFFF5722,
                    'accent_red' => 0xFFF44336,
                    'accent_green' => 0xFF4CAF50,
                    'accent_blue' => 0xFF2196F3,
                    'accent_amber' => 0xFFFFC107,
                    'input_fill' => 0xFFF5F5F5,
                    'input_border' => 0xFFE0E0E0,
                    'input_hint' => 0xFF9E9E9E,
                    'slider_background' => 0xFFF5F5F5,
                    'slider_dot_active' => 0xFF43A196,
                    'slider_dot_inactive' => 0xFFE0E0E0,
                    'color_shadow' => 0x40000000,
                    'color_overlay' => 0x80000000,
                ],
            ],
            'ocean_blue' => [
                'name' => 'Ocean Blue',
                'colors' => [
                    'primary_dark_blue' => 0xFF0D47A1,
                    'primary_teal' => 0xFF1976D2,
                    'secondary_teal' => 0xFF42A5F5,
                    'gray_light' => 0xFFF5F5F5,
                    'gray_medium' => 0xFFE0E0E0,
                    'gray_dark' => 0xFF757575,
                    'text_dark' => 0xFF212121,
                    'text_light' => 0xFFFFFFFF,
                    'text_muted' => 0xFF9E9E9E,
                    'color_error' => 0xFFE53935,
                    'color_success' => 0xFF43A047,
                    'color_warning' => 0xFFFFA000,
                    'color_info' => 0xFF1E88E5,
                    'color_surface' => 0xFFFFFFFF,
                    'color_background' => 0xFFE3F2FD,
                    'color_card' => 0xFFFFFFFF,
                    'color_divider' => 0xFFBBDEFB,
                    'status_pending' => 0xFFFF9800,
                    'status_confirmed' => 0xFF2196F3,
                    'status_in_progress' => 0xFFFFC107,
                    'status_completed' => 0xFF4CAF50,
                    'status_cancelled' => 0xFFF44336,
                    'status_default' => 0xFF9E9E9E,
                    'button_primary' => 0xFF1976D2,
                    'button_secondary' => 0xFF757575,
                    'button_danger' => 0xFFE53935,
                    'button_success' => 0xFF43A047,
                    'button_text' => 0xFFFFFFFF,
                    'button_text_dark' => 0xFF212121,
                    'chat_bubble_me' => 0xFF1976D2,
                    'chat_bubble_other' => 0xFFE0E0E0,
                    'chat_background' => 0xFFE3F2FD,
                    'chat_icon' => 0xFF1976D2,
                    'rating_active' => 0xFFFFC107,
                    'rating_inactive' => 0xFFE0E0E0,
                    'nav_active' => 0xFF1976D2,
                    'nav_inactive' => 0xFF9E9E9E,
                    'accent_teal' => 0xFF00ACC1,
                    'accent_orange' => 0xFFFF5722,
                    'accent_red' => 0xFFF44336,
                    'accent_green' => 0xFF4CAF50,
                    'accent_blue' => 0xFF2196F3,
                    'accent_amber' => 0xFFFFC107,
                    'input_fill' => 0xFFE3F2FD,
                    'input_border' => 0xFFBBDEFB,
                    'input_hint' => 0xFF9E9E9E,
                    'slider_background' => 0xFFE3F2FD,
                    'slider_dot_active' => 0xFF1976D2,
                    'slider_dot_inactive' => 0xFFBBDEFB,
                    'color_shadow' => 0x40000000,
                    'color_overlay' => 0x80000000,
                ],
            ],
            'dark_mode' => [
                'name' => 'Dark Mode',
                'colors' => [
                    'primary_dark_blue' => 0xFF121212,
                    'primary_teal' => 0xFF4DB6AC,
                    'secondary_teal' => 0xFF80CBC4,
                    'gray_light' => 0xFF424242,
                    'gray_medium' => 0xFF616161,
                    'gray_dark' => 0xFF9E9E9E,
                    'text_dark' => 0xFFFFFFFF,
                    'text_light' => 0xFF000000,
                    'text_muted' => 0xFFBDBDBD,
                    'color_error' => 0xFFEF5350,
                    'color_success' => 0xFF66BB6A,
                    'color_warning' => 0xFFFFCA28,
                    'color_info' => 0xFF42A5F5,
                    'color_surface' => 0xFF1E1E1E,
                    'color_background' => 0xFF121212,
                    'color_card' => 0xFF2D2D2D,
                    'color_divider' => 0xFF424242,
                    'status_pending' => 0xFFFFB74D,
                    'status_confirmed' => 0xFF64B5F6,
                    'status_in_progress' => 0xFFFFD54F,
                    'status_completed' => 0xFF81C784,
                    'status_cancelled' => 0xFFE57373,
                    'status_default' => 0xFF9E9E9E,
                    'button_primary' => 0xFF4DB6AC,
                    'button_secondary' => 0xFF757575,
                    'button_danger' => 0xFFEF5350,
                    'button_success' => 0xFF66BB6A,
                    'button_text' => 0xFF000000,
                    'button_text_dark' => 0xFFFFFFFF,
                    'chat_bubble_me' => 0xFF4DB6AC,
                    'chat_bubble_other' => 0xFF424242,
                    'chat_background' => 0xFF121212,
                    'chat_icon' => 0xFF4DB6AC,
                    'rating_active' => 0xFFFFD54F,
                    'rating_inactive' => 0xFF616161,
                    'nav_active' => 0xFF4DB6AC,
                    'nav_inactive' => 0xFF757575,
                    'accent_teal' => 0xFF26A69A,
                    'accent_orange' => 0xFFFF7043,
                    'accent_red' => 0xFFE57373,
                    'accent_green' => 0xFF81C784,
                    'accent_blue' => 0xFF64B5F6,
                    'accent_amber' => 0xFFFFD54F,
                    'input_fill' => 0xFF2D2D2D,
                    'input_border' => 0xFF424242,
                    'input_hint' => 0xFF757575,
                    'slider_background' => 0xFF2D2D2D,
                    'slider_dot_active' => 0xFF4DB6AC,
                    'slider_dot_inactive' => 0xFF424242,
                    'color_shadow' => 0x60000000,
                    'color_overlay' => 0xA0000000,
                ],
            ],
        ];
    }

    /**
     * Apply a preset theme
     */
    public function applyPreset(string $presetKey): void
    {
        $presets = self::getPresetThemes();

        if (!isset($presets[$presetKey])) {
            throw new \InvalidArgumentException("Unknown preset: {$presetKey}");
        }

        $preset = $presets[$presetKey];
        $this->fill($preset['colors']);
        $this->brightness = $presetKey === 'dark_mode' ? 'dark' : 'light';
    }
}
