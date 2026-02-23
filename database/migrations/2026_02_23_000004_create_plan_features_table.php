<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->enum('feature_key', [
                'links_per_month',
                'custom_domains_count',
                'domain_whitelist',
                'custom_subdomain',
                'meta_tracking',
                'google_tracking',
                'analytics_level',
                'qrcode',
                'password_protected_links',
                'geo_metrics',
                'bot_detection',
                'spam_detection',
                'team_members_count',
                'link_expiry',
                'bulk_links',
                'campaign_tracking',
            ]);
            $table->string('feature_value');
            $table->timestamps();

            $table->index(['plan_id', 'feature_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
