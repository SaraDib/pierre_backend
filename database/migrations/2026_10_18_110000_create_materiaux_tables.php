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
        // Section header (singleton row, same pattern as `savoir_faire_sections`)
        Schema::create('materiaux_sections', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->string('title_em')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Categories (orderable, can be hidden)
        Schema::create('materiaux_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        // Materials (orderable, can be hidden). `slug` is the stable key used by
        // Accueil (`arch_material`) and Savoir-faire (`mat_key`).
        Schema::create('materiaux_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()
                ->constrained('materiaux_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('finishes')->nullable();
            $table->string('texture_type', 20)->default('veins');
            $table->string('base', 7)->default('#cccccc');
            $table->string('vein', 7)->default('#777777');
            $table->string('tone', 7)->default('#aaaaaa');
            $table->string('freq', 30)->default('0.01');
            $table->unsignedSmallInteger('seed')->default(1);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiaux_items');
        Schema::dropIfExists('materiaux_categories');
        Schema::dropIfExists('materiaux_sections');
    }
};
