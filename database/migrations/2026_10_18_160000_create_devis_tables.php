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
        // 1. Table for Devis Section content
        Schema::create('devis_section', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->default('Devis sur mesure');
            $table->string('title_prefix')->default('Donnez vie à');
            $table->string('title_emphasis')->default('vos projets de pierre.');
            $table->text('description')->nullable();
            $table->string('stat1_value')->default('24h');
            $table->string('stat1_label')->default('Délai de réponse');
            $table->string('stat2_value')->default('Gratuit');
            $table->string('stat2_label')->default('Étude & chiffrage');
            $table->string('button_text')->default('Envoyer via WhatsApp');
            $table->string('note_text')->default('Le message se prépare automatiquement dans WhatsApp, prêt à être expédié.');
            $table->timestamps();
        });

        // 2. Table for quote requests submitted via WhatsApp / site
        Schema::create('devis_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('project_type')->default('Autre aménagement');
            $table->text('message')->nullable();
            $table->string('status')->default('nouveau'); // nouveau | en_cours | traite | archive
            $table->text('admin_notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_requests');
        Schema::dropIfExists('devis_section');
    }
};
