<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RECONSTRUCTION : le fichier original portait ce nom ("add_offer_id_to_spotlights_table")
 * mais son code était un copier-coller de la migration des champs de paiement sur
 * "bookings" — il ne touchait jamais réellement à "spotlights". Comme toutes les
 * colonnes qu'il ajoutait existaient déjà (grâce aux gardes hasColumn), il ne plantait
 * pas, mais la colonne "offer_id" n'a jamais été créée sur "spotlights".
 *
 * Ci-dessous une version qui fait ce que le nom du fichier annonce. Vérifie que
 * "nullable + nullOnDelete" correspond bien à ce qu'attend ton code applicatif
 * (ex. App\Models\Spotlight) avant de l'exécuter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spotlights', function (Blueprint $table) {
            if (!Schema::hasColumn('spotlights', 'offer_id')) {
                $table->foreignId('offer_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('offers')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('spotlights', function (Blueprint $table) {
            if (Schema::hasColumn('spotlights', 'offer_id')) {
                $table->dropConstrainedForeignId('offer_id');
            }
        });
    }
};
