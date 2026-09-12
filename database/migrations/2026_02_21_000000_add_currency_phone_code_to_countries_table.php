<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FIX : "currency" et "phone_code" sont déjà ajoutés par
        // 2025_09_11_add_hero_fields_to_countries_table.php qui s'exécute avant.
        // On ajoute des gardes hasColumn pour éviter "Duplicate column name".
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'currency')) {
                $table->string('currency', 3)->nullable()->after('code');
            }
            if (!Schema::hasColumn('countries', 'phone_code')) {
                $table->string('phone_code', 8)->nullable()->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'phone_code')) {
                $table->dropColumn('phone_code');
            }
            if (Schema::hasColumn('countries', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};
