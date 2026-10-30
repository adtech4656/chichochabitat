<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ---------------- Currencies & live rates ---------------- */

        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 3)->unique();          // XOF, USD, EUR, NGN, GHS
            $table->string('name');
            $table->string('symbol', 8);
            $table->unsignedSmallInteger('decimals')->default(0);
            // How many units of this currency one XOF buys (XOF = 1).
            $table->decimal('rate_to_base', 18, 8)->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_base')->default(false);
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('exchange_rate_logs', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 3)->index();
            $table->decimal('rate_to_base', 18, 8);
            $table->string('source', 32)->default('manual'); // manual|api
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        /* ---------------- Property sub-type ---------------- */

        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'sub_type')) {
                // bungalow | villa | duplex | apartment | studio | ...
                $table->string('sub_type', 32)->nullable()->after('type')->index();
            }
        });

        /* ---------------- Offers ---------------- */

        Schema::create('offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');                 // in base currency
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->string('status', 24)->default('pending')->index(); // pending|accepted|rejected|withdrawn
            $table->text('message')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        /* ---------------- Conversations & messages ---------------- */

        Schema::create('conversations', function (Blueprint $table): void {
            $table->id();
            // customer_agent | department | support
            $table->string('type', 32)->default('customer_agent')->index();
            $table->string('subject')->nullable();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('department', 32)->nullable()->index();
            $table->string('status', 24)->default('open')->index(); // open|closed
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->unique(['conversation_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();
        });

        /* ---------------- Verification tasks ---------------- */

        Schema::create('verification_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            // documents | media | legal | identity | final
            $table->string('stage', 32)->index();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('open')->index(); // open|in_progress|done|blocked
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        /* ---------------- Marketing events ---------------- */

        Schema::create('site_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 48)->index();   // page_view|search|offer|signup
            $table->string('path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 64)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });

        /* ---------------- Customer care tickets ---------------- */

        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('category', 48)->default('general');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('status', 24)->default('open')->index();
            $table->string('priority', 16)->default('normal');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('site_events');
        Schema::dropIfExists('verification_tasks');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('exchange_rate_logs');
        Schema::dropIfExists('currencies');

        Schema::table('properties', function (Blueprint $table): void {
            $table->dropColumn('sub_type');
        });
    }
};
