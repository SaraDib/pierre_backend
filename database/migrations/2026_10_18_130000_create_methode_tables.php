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
        Schema::create('methode_sections', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable()->default('Notre méthode');
            $table->string('title')->nullable()->default('Du bloc brut');
            $table->string('title_em')->nullable()->default("à l'ouvrage fini.");
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Steps of the method
        Schema::create('methode_items', function (Blueprint $table) {
            $table->id();
            $table->string('step_number')->nullable(); // '01', '02', '1', etc.
            $table->string('title');
            $table->text('short_description');
            $table->text('description')->nullable();
            $table->string('duration')->nullable(); // e.g. '24h - 48h'
            $table->string('icon')->nullable(); // e.g. 'Ruler', 'FileText', 'Hammer', 'Truck'
            $table->string('image')->nullable();
            $table->unsignedInteger('position')->default(0);
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
        Schema::dropIfExists('methode_items');
        Schema::dropIfExists('methode_sections');
    }
};
