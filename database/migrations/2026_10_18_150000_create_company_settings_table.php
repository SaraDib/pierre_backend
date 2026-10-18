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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('SUTRAVOMAR');
            $table->string('tagline')->default('STONE LUXE');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable()->default('/logo-gold.png');
            $table->string('logo_footer_url')->nullable();
            $table->string('phone')->default('+212 6 00 00 00 00');
            $table->string('phone_secondary')->nullable();
            $table->string('whatsapp_number')->default('212600000000');
            $table->string('email')->default('contact@sutravomar-stone.ma');
            $table->string('address')->default('Atelier & Showroom, Maroc');
            $table->string('city')->nullable()->default('Casablanca');
            $table->string('country')->nullable()->default('Maroc');
            $table->string('opening_hours')->nullable()->default('Lun - Sam : 08h30 - 18h30');
            $table->string('intervention_zone')->nullable()->default('Intervention dans tout le Maroc');
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('maps_url')->nullable();
            $table->string('copyright')->default('© 2026 SUTRAVOMAR STONE LUXE. Tous droits réservés.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
