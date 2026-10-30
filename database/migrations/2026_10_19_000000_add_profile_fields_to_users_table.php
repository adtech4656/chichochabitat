<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 32)->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code', 2)->default('CI')->after('phone');
            }

            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 8)->default('fr')->after('country_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'country_code', 'locale']);
        });
    }
};
