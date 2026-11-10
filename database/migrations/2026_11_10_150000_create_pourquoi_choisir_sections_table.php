<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pourquoi_choisir_sections', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->default('POURQUOI NOUS CHOISIR ?');
            $table->string('title_line1')->default("L'excellence");
            $table->string('title_line2')->default('au cœur de chaque projet.');
            $table->text('description')->nullable();
            $table->string('stat1_number')->default('+500');
            $table->string('stat1_label')->default('Projets réalisés');
            $table->string('stat2_number')->default('100%');
            $table->string('stat2_label')->default('Clients satisfaits');
            $table->string('stat3_number')->default('15 ans');
            $table->string('stat3_label')->default("D'expérience");
            $table->string('button_text')->default('En savoir plus');
            $table->string('button_link')->default('#materiaux');
            $table->string('image')->default('/images/pourquoi-nous-choisir-escalier.jpg');
            $table->string('image_alt')->default('Escalier monumental en marbre avec éclairage LED sous chaque marche');
            $table->string('quote_line1')->default('La pierre');
            $table->string('quote_line2')->default('révèle le caractère');
            $table->string('quote_line3')->default('de vos espaces');
            $table->json('categories')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pourquoi_choisir_sections');
    }
};

