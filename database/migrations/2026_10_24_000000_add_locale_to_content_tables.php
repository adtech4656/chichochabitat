<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-editable content becomes per-language.
 *
 * A page or setting is addressed by (slug, locale) / (key, locale). Readers
 * fall back to the application fallback locale whenever a translation is
 * missing, so an English-only value still serves French visitors.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropUnique('pages_slug_unique');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->string('locale', 5)->default('en')->after('slug')->index();
            $table->unique(['slug', 'locale']);
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropUnique('site_settings_key_unique');
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('locale', 5)->default('en')->after('key')->index();
            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropUnique(['slug', 'locale']);
            $table->dropIndex(['locale']);
            $table->dropColumn('locale');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->unique('slug');
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropUnique(['key', 'locale']);
            $table->dropIndex(['locale']);
            $table->dropColumn('locale');
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->unique('key');
        });
    }
};
