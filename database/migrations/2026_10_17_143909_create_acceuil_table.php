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
        Schema::create('acceuil', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable()->default('Marbre · Granit · Pierre naturelle — Maroc');
            $table->string('title_line1')->nullable()->default('La pierre,');
            $table->string('title_line2')->nullable()->default('taillée');
            $table->string('title_line3')->nullable()->default('pour durer.');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable()->default('/videos/logo-reveal.mp4');
            $table->string('arch_badge')->nullable()->default('Collection');
            $table->string('arch_title')->nullable()->default('Beige de Taza, poli');
            $table->string('arch_material')->nullable()->default('travertin');
            $table->string('arch_image')->nullable();
            $table->string('stat1_number')->nullable()->default('30+');
            $table->string('stat1_label')->nullable()->default('Variétés de pierres');
            $table->string('stat2_number')->nullable()->default('100%');
            $table->string('stat2_label')->nullable()->default('Sur mesure');
            $table->string('stat3_number')->nullable()->default('Maroc');
            $table->string('stat3_label')->nullable()->default('Livraison & pose');
            $table->json('marquee_words')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceuil');
    }
};
