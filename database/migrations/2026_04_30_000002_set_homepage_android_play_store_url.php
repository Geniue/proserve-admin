<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        $homepage = DB::table('pages')->where('slug', 'home')->first();

        if (! $homepage) {
            return;
        }

        $blocks = json_decode($homepage->content_blocks ?? '[]', true);

        if (! is_array($blocks)) {
            $blocks = [];
        }

        if (! isset($blocks['hero']) || ! is_array($blocks['hero'])) {
            $blocks['hero'] = [];
        }

        $currentUrl = trim((string) ($blocks['hero']['google_play_url'] ?? ''));

        if ($currentUrl !== '' && $currentUrl !== '#') {
            return;
        }

        $blocks['hero']['google_play_url'] = Page::ANDROID_PLAY_STORE_URL;

        DB::table('pages')
            ->where('id', $homepage->id)
            ->update([
                'content_blocks' => json_encode($blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        $homepage = DB::table('pages')->where('slug', 'home')->first();

        if (! $homepage) {
            return;
        }

        $blocks = json_decode($homepage->content_blocks ?? '[]', true);

        if (! is_array($blocks)) {
            return;
        }

        if (! isset($blocks['hero']) || ! is_array($blocks['hero'])) {
            return;
        }

        if (($blocks['hero']['google_play_url'] ?? null) !== Page::ANDROID_PLAY_STORE_URL) {
            return;
        }

        $blocks['hero']['google_play_url'] = '#';

        DB::table('pages')
            ->where('id', $homepage->id)
            ->update([
                'content_blocks' => json_encode($blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
    }
};
