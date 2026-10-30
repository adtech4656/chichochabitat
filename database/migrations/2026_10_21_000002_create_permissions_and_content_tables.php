<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ---------------------------------------------------------------
         | Permissions
         | ------------------------------------------------------------- */

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();      // e.g. properties.approve
            $table->string('label');              // human readable
            $table->string('group', 48)->index(); // nav grouping
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('role', 32)->index();
            $table->string('permission_key');
            $table->timestamps();

            $table->unique(['role', 'permission_key']);
        });

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 64)->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('users', 'legacy_permissions')) {
                // Per-user grants on top of the role. Admin-only to change.
                $table->json('legacy_permissions')->nullable()->after('role');
            }
        });

        /* ---------------------------------------------------------------
         | Editable site content
         | ------------------------------------------------------------- */

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 48)->index();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 24)->default('text'); // text|textarea|number|boolean|json
            $table->string('label');
            $table->string('hint')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('group', 48)->default('company')->index();
            $table->longText('body')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');

        Schema::table('users', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['username', 'legacy_permissions', 'permissions'],
                fn (string $column): bool => Schema::hasColumn('users', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
