<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('title_translations')->nullable()->after('meta_description');
            $table->json('excerpt_translations')->nullable()->after('title_translations');
            $table->json('content_translations')->nullable()->after('excerpt_translations');
            $table->json('seo_translations')->nullable()->after('content_translations');
            $table->json('content_blocks')->nullable()->after('seo_translations');
            $table->json('schema_markup')->nullable()->after('content_blocks');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'title_translations',
                'excerpt_translations',
                'content_translations',
                'seo_translations',
                'content_blocks',
                'schema_markup',
            ]);
        });
    }
};
