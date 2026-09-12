<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FIX : la table "users" (schéma consolidé) n'a pas de colonne "bio".
        // On positionne après "avatar" à la place, et on ajoute une garde hasColumn.
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->nullable()->after('avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nationality')) {
                $table->dropColumn('nationality');
            }
        });
    }
};
