<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('description');
            }

            if (! Schema::hasColumn('properties', 'gallery')) {
                $table->json('gallery')->nullable()->after('cover_image');
            }

            if (! Schema::hasColumn('properties', 'tags')) {
                $table->json('tags')->nullable()->after('gallery');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->dropColumn(['cover_image', 'gallery', 'tags']);
        });
    }
};
