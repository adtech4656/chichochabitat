<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            if (! Schema::hasColumn('conversations', 'chat_state')) {
                $table->string('chat_state', 32)->default('pending')->after('status')->index();
            }

            if (! Schema::hasColumn('conversations', 'last_state_change_at')) {
                $table->timestamp('last_state_change_at')->nullable()->after('last_message_at');
            }
        });

        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('verification_code', 16)->unique();
            $table->string('location')->nullable();
            $table->string('status', 24)->default('scheduled')->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('property_document_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_reissued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_access_code', 16)->unique();
            $table->string('status', 24)->default('active')->index();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_document_accesses');
        Schema::dropIfExists('appointments');

        Schema::table('conversations', function (Blueprint $table): void {
            if (Schema::hasColumn('conversations', 'chat_state')) {
                $table->dropColumn('chat_state');
            }

            if (Schema::hasColumn('conversations', 'last_state_change_at')) {
                $table->dropColumn('last_state_change_at');
            }
        });
    }
};
