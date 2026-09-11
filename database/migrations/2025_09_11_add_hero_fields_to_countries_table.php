<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // Champs manquants dans la migration initiale (présents dans $fillable)
            if (! Schema::hasColumn('countries', 'currency')) {
                $table->string('currency')->nullable()->after('continent');
            }
            if (! Schema::hasColumn('countries', 'phone_code')) {
                $table->string('phone_code', 10)->nullable()->after('currency');
            }

            // Nouveaux champs pour le hero dynamique
            $table->string('region')->nullable()->after('continent');         // "Afrique de l'Ouest"
            $table->string('hero_tagline')->nullable()->after('region');      // "Du delta de l'Ouémé aux plateaux de l'Atakora"
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['region', 'hero_tagline']);

            // On ne drop pas currency/phone_code car ils peuvent exister
            // depuis une autre migration selon les environnements
        });
    }
};