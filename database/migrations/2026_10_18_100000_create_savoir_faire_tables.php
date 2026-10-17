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
        // Section header (singleton row, same pattern as `acceuil`)
        Schema::create('savoir_faire_sections', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->string('title_em')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Expertise layers (orderable, can be hidden)
        Schema::create('savoir_faire_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('position')->default(0);
            $table->string('mat_key', 50)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('savoir_faire_items');
        Schema::dropIfExists('savoir_faire_sections');
    }
};
