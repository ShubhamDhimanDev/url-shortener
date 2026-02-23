<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('color', 7)->default('#6366F1');
            $table->timestamps();
        });

        Schema::create('link_tag_pivot', function (Blueprint $table) {
            $table->foreignId('link_id')->constrained('links')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('link_tags')->cascadeOnDelete();

            $table->primary(['link_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_tag_pivot');
        Schema::dropIfExists('link_tags');
    }
};
