<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeChangeLog extends Model
{
    use HasFactory;

    protected $table = 'theme_change_logs';

    public $timestamps = false;

    protected $fillable = [
        'theme_config_id',
        'admin_id',
        'field_changed',
        'old_value',
        'new_value',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function themeConfig(): BelongsTo
    {
        return $this->belongsTo(ThemeConfig::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Log a theme field change
     */
    public static function logChange(
        ThemeConfig $theme,
        string $field,
        mixed $oldValue,
        mixed $newValue
    ): self {
        return static::create([
            'theme_config_id' => $theme->id,
            'admin_id' => auth()->id(),
            'field_changed' => $field,
            'old_value' => is_numeric($oldValue) ? ThemeConfig::intToHex($oldValue) : (string) $oldValue,
            'new_value' => is_numeric($newValue) ? ThemeConfig::intToHex($newValue) : (string) $newValue,
            'changed_at' => now(),
        ]);
    }

    /**
     * Log multiple field changes at once
     */
    public static function logChanges(ThemeConfig $theme, array $changes): void
    {
        foreach ($changes as $field => $values) {
            if (isset($values['old']) && isset($values['new']) && $values['old'] !== $values['new']) {
                static::logChange($theme, $field, $values['old'], $values['new']);
            }
        }
    }

    /**
     * Get human-readable field name
     */
    public function getFieldLabel(): string
    {
        $labels = [];
        foreach (ThemeConfig::getColorGroups() as $group => $fields) {
            foreach ($fields as $key => $label) {
                $labels[$key] = "{$group}: {$label}";
            }
        }

        return $labels[$this->field_changed] ?? ucwords(str_replace('_', ' ', $this->field_changed));
    }
}
