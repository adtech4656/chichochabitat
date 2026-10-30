<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'permissions') && ! Schema::hasColumn('users', 'legacy_permissions')) {
            Schema::table('users', function ($table): void {
                $table->renameColumn('permissions', 'legacy_permissions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'legacy_permissions') && ! Schema::hasColumn('users', 'permissions')) {
            Schema::table('users', function ($table): void {
                $table->renameColumn('legacy_permissions', 'permissions');
            });
        }
    }
};
