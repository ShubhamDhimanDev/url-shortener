<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('short_code')->unique();
            $table->text('destination_url');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_password_protected')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('meta_pixel_id')->nullable();
            $table->string('google_tag_id')->nullable();
            $table->boolean('is_bot_protection_enabled')->default(false);
            $table->boolean('is_spam_detected')->default(false);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->unsignedBigInteger('unique_clicks_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('short_code');
            $table->index('user_id');
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
