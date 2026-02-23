<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('links')->cascadeOnDelete();
            $table->string('session_hash', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('device_type', ['desktop', 'mobile', 'tablet', 'bot', 'unknown'])->default('unknown');
            $table->string('os')->nullable();
            $table->string('os_version')->nullable();
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('referrer_domain')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('bot_name')->nullable();
            $table->boolean('is_unique')->default(false);
            $table->timestamp('clicked_at');
            $table->timestamps();

            $table->index('link_id');
            $table->index('clicked_at');
            $table->index('is_bot');
            $table->index('country_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
    }
};
