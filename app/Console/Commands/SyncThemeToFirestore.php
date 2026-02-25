<?php

namespace App\Console\Commands;

use App\Models\ThemeConfig;
use Illuminate\Console\Command;

class SyncThemeToFirestore extends Command
{
    protected $signature = 'theme:sync {id?}';
    protected $description = 'Sync theme configuration to Firestore';

    public function handle()
    {
        $id = $this->argument('id');
        
        if ($id) {
            $theme = ThemeConfig::find($id);
            if (!$theme) {
                $this->error("Theme with ID {$id} not found");
                return 1;
            }
            $themes = [$theme];
        } else {
            $themes = ThemeConfig::all();
        }

        foreach ($themes as $theme) {
            $this->info("Syncing theme: {$theme->name} (firebase_id: {$theme->firebase_id})");
            
            try {
                $theme->pushToFirestore('update');
                $this->info("✓ Successfully synced to Firestore collection: {$theme->getFirestoreCollection()}");
            } catch (\Exception $e) {
                $this->error("✗ Failed: " . $e->getMessage());
            }
        }

        return 0;
    }
}
