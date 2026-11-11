<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Types de matériaux (Marbre, Granit, Onyx…) : niveau 1 du catalogue.
     *   /detailsmateriaux                 → liste des types
     *   /detailsmateriaux/{type}          → variétés du type (ex : tous les marbres)
     *   /detailsmateriaux/{type}/{slug}   → fiche d'une variété
     */
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->string('name');                    // ex : Marbre
            $table->string('subtitle')->nullable();    // ligne sous le nom sur la carte (vide = « N variétés »)
            $table->text('description')->nullable();
            $table->string('image')->nullable();       // texture de la carte
            $table->string('hero_image')->nullable();  // bandeau de la page du type
            $table->string('fallback_gradient')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        Schema::table('catalog_materials', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('catalog_categories')
                ->nullOnDelete();
        });

        // Reprise des données : un type par valeur distincte de `type`.
        $materials = DB::table('catalog_materials')->orderBy('position')->orderBy('id')->get();
        $position = 0;
        $map = [];

        foreach ($materials as $m) {
            $name = trim($m->type ?: $m->name);
            if ($name === '') {
                continue;
            }

            if (! isset($map[$name])) {
                $base = Str::slug($name) ?: 'type';
                $slug = $base;
                $i = 2;
                while (DB::table('catalog_categories')->where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }

                $map[$name] = DB::table('catalog_categories')->insertGetId([
                    'slug' => $slug,
                    'position' => ++$position,
                    'name' => $name,
                    'subtitle' => null,
                    'description' => null,
                    'image' => $m->image,
                    'hero_image' => $m->hero_image,
                    'fallback_gradient' => $m->fallback_gradient,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('catalog_materials')->where('id', $m->id)->update(['category_id' => $map[$name]]);
        }
    }

    public function down(): void
    {
        Schema::table('catalog_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('catalog_categories');
    }
};

