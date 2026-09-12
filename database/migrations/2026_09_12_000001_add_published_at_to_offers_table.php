<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX : "published_at" existait dans l'ancienne migration originale
 * (2024_01_01_000002_create_offers_table) mais n'a pas été reportée dans
 * 0000_00_00_000000_create_all_base_tables. HomeController::featured_offers
 * trie dessus (ORDER BY published_at desc) -> colonne manquante en prod.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (!Schema::hasColumn('offers', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
        });

        // Backfill : les offres déjà publiées reçoivent created_at comme date
        // de publication, sinon elles seraient toutes exclues/mal triées.
        if (Schema::hasColumn('offers', 'published_at')) {
            \Illuminate\Support\Facades\DB::table('offers')
                ->where('status', 'published')
                ->whereNull('published_at')
                ->update(['published_at' => \Illuminate\Support\Facades\DB::raw('created_at')]);
        }
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });
    }
};
