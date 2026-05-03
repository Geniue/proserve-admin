<?php

use App\Support\TransparentImageTrimmer;
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
            return;
        }

        $paths = array_filter([
            $blocks['logo']['dark_image_url'] ?? null,
            $blocks['logo']['white_image_url'] ?? null,
            $blocks['footer']['logo_image_url'] ?? null,
        ]);

        foreach (array_unique($paths) as $path) {
            TransparentImageTrimmer::trimPublicDiskImage($path);
        }
    }

    public function down(): void
    {
        //
    }
};
