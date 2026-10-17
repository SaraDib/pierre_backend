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
        // 1. Page & Section header (Singleton row, matching earlier sections)
        Schema::create('realisations_sections', function (Blueprint $table) {
            $table->id();
            $table->string('hero_eyebrow')->nullable()->default("Portfolio d'Exception · Maroc");
            $table->string('hero_title')->nullable()->default('Toutes nos');
            $table->string('hero_title_em')->nullable()->default('réalisations.');
            $table->text('hero_description')->nullable();

            $table->string('bento_eyebrow')->nullable()->default('Réalisations');
            $table->string('bento_title')->nullable()->default('Des projets qui');
            $table->string('bento_title_em')->nullable()->default('portent notre signature.');
            $table->text('bento_description')->nullable();

            $table->string('cta_title')->nullable()->default('Un projet sur mesure');
            $table->string('cta_title_em')->nullable()->default('en pierre naturelle ?');
            $table->text('cta_description')->nullable();
            $table->timestamps();
        });

        // 2. Categories (Façades, Escaliers, Cuisines, etc.)
        Schema::create('realisations_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('badge_label')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        // 3. Projects
        Schema::create('realisations_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()
                ->constrained('realisations_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('short_title')->nullable();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('stone')->nullable();
            $table->string('finish')->nullable();
            $table->string('location')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('grid_class', 20)->nullable(); // 't1', 't2', 't3', 't4', 't5' for Bento
            $table->boolean('is_featured')->default(false); // Displayed on Accueil Bento
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
            $table->index(['is_featured', 'position']);
        });

        // 4. Secondary gallery images per project
        Schema::create('realisations_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisation_item_id')
                ->constrained('realisations_items')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['realisation_item_id', 'position']);
        });

        // 5. Many-to-Many link between Projects and Materials
        Schema::create('realisations_materiaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisation_item_id')
                ->constrained('realisations_items')->cascadeOnDelete();
            $table->foreignId('materiaux_item_id')
                ->constrained('materiaux_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['realisation_item_id', 'materiaux_item_id'], 'realisation_materiau_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisations_materiaux');
        Schema::dropIfExists('realisations_images');
        Schema::dropIfExists('realisations_items');
        Schema::dropIfExists('realisations_categories');
        Schema::dropIfExists('realisations_sections');
    }
};
