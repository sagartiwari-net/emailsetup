<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_list_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('status')->default('active');
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['subscriber_list_id', 'email']);
            $table->index(['subscriber_list_id', 'status']);
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscriber_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('template_slug');
            $table->string('subject')->nullable();
            $table->string('schedule_type')->default('now');
            $table->json('schedule_config')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('run_number')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('last_batch_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_run_at']);
        });

        Schema::create('campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_log_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('run_number')->default(1);
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'subscriber_id', 'run_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_logs');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('subscriber_lists');
    }
};
