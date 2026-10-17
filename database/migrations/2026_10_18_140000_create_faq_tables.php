<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Singleton section header
        Schema::create('faq_sections', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable()->default('Questions fréquentes');
            $table->string('title')->nullable()->default('Tout ce que vous');
            $table->string('title_em')->nullable()->default('devez savoir.');
            $table->text('description')->nullable();
            $table->string('cta_text')->nullable()->default('Poser une question');
            $table->string('cta_link')->nullable(); // e.g. custom link or falls back to WhatsApp
            $table->timestamps();
        });

        // 2. FAQ Categories (orderable, can be toggled)
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        // 3. FAQ Items (Questions & Answers)
        Schema::create('faq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()
                ->constrained('faq_categories')->nullOnDelete();
            $table->text('question');
            $table->mediumText('answer');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
            $table->index(['is_featured', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_items');
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('faq_sections');
    }
};
