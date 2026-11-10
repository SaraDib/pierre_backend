<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acceuil', function (Blueprint $table) {
            if (! Schema::hasColumn('acceuil', 'features_bar')) {
                $table->json('features_bar')->nullable()->after('marquee_words');
            }
        });
    }

    public function down(): void
    {
        Schema::table('acceuil', function (Blueprint $table) {
            if (Schema::hasColumn('acceuil', 'features_bar')) {
                $table->dropColumn('features_bar');
            }
        });
    }
};

