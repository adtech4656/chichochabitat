<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table): void {
            $table->id();

            // Multi-country / multi-tenant from day one (see brief §10, §11).
            $table->char('country_code', 2)->default('CI')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();

            $table->string('reference')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // sale | rent
            $table->string('intent', 16)->default('sale')->index();
            // House | Land | Shop | Hotel
            $table->string('type', 24)->index();

            $table->string('district');
            $table->string('city');

            $table->unsignedBigInteger('price');
            $table->char('currency', 3)->default('XOF');
            $table->unsignedBigInteger('platform_fee')->default(0);

            $table->unsignedSmallInteger('bedrooms')->default(0);
            $table->unsignedSmallInteger('bathrooms')->default(0);
            $table->unsignedInteger('area')->default(0);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // pending | in_review | approved | rejected | needs_info
            $table->string('status', 24)->default('pending')->index();

            $table->unsignedSmallInteger('required_shots')->default(8);
            $table->unsignedSmallInteger('captured_shots')->default(0);
            $table->unsignedSmallInteger('documents_count')->default(0);
            $table->unsignedInteger('gps_delta_m')->default(0);

            // pending | approved | rejected
            $table->string('agent_kyc_status', 16)->default('pending');

            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->unsignedInteger('views')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
