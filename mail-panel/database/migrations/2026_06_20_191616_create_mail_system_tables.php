<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('daily_limit')->default(15);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('tenant')->after('password');
            $table->foreignId('tenant_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });

        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('domain_name')->unique();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('dkim_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('key_prefix', 12);
            $table->string('key_hash');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['key_prefix', 'is_active']);
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('subject');
            $table->text('html_body');
            $table->text('text_body')->nullable();
            $table->string('type')->default('transactional');
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_id')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_key_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to_email');
            $table->string('subject');
            $table->string('template_slug')->nullable();
            $table->string('status')->default('queued');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('daily_send_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_send_counts');
        Schema::dropIfExists('mail_logs');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('tenants');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn('role');
        });
    }
};
