<?php

namespace Database\Seeders;

use App\Models\ThemeConfig;
use Illuminate\Database\Seeder;

class ThemeConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the default ProServe preset
        $presets = ThemeConfig::getPresetThemes();
        $defaultPreset = $presets['proserve_default'];

        // Create the default theme
        ThemeConfig::updateOrCreate(
            ['firebase_id' => 'primary_theme_v1'],
            array_merge([
                'name' => 'ProServe Default',
                'name_ar' => 'بروسيرف الافتراضي',
                'version' => 1,
                'is_active' => true,
                'brightness' => 'light',
            ], $defaultPreset['colors'])
        );

        $this->command->info('Default ProServe theme created successfully!');

        // Optionally create the other presets as inactive themes
        $otherPresets = [
            'ocean_blue' => ['name' => 'Ocean Blue', 'name_ar' => 'الأزرق المحيط', 'brightness' => 'light'],
            'dark_mode' => ['name' => 'Dark Mode', 'name_ar' => 'الوضع الداكن', 'brightness' => 'dark'],
        ];

        foreach ($otherPresets as $key => $meta) {
            if (isset($presets[$key])) {
                ThemeConfig::updateOrCreate(
                    ['firebase_id' => $key . '_theme'],
                    array_merge([
                        'name' => $meta['name'],
                        'name_ar' => $meta['name_ar'],
                        'version' => 1,
                        'is_active' => false,
                        'brightness' => $meta['brightness'],
                    ], $presets[$key]['colors'])
                );

                $this->command->info("{$meta['name']} theme created successfully!");
            }
        }
    }
}
