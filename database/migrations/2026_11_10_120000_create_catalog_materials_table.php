<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalogue des matériaux : cartes du carrousel « Nos matériaux » (accueil),
     * grille /detailsmateriaux et pages /detailsmateriaux/{slug}.
     *
     * Indépendant de `materiaux_items`, qui alimente les textures SVG
     * (carte arche de l'accueil, couches du Savoir-faire).
     */
    public function up(): void
    {
        Schema::create('catalog_materials', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->string('type', 100);               // onglet de filtre
            $table->string('name');                    // ligne en gras sur la carte
            $table->string('variant')->nullable();     // ligne sous le nom
            $table->text('description')->nullable();
            $table->string('image')->nullable();       // texture plein cadre de la carte
            $table->string('hero_image')->nullable();  // grande image de la page détail
            $table->json('gallery')->nullable();       // [{ src, alt }]
            $table->string('origin')->nullable();
            $table->string('finish')->nullable();
            $table->string('thickness')->nullable();
            $table->json('uses')->nullable();          // ["Escaliers", ...]
            $table->text('maintenance')->nullable();
            $table->string('fallback_gradient')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_materials');
    }
};

