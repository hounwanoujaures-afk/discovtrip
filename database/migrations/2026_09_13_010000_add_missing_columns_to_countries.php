<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (! Schema::hasColumn('countries', 'region')) {
                $table->string('region')->nullable()->after('continent');
            }
            if (! Schema::hasColumn('countries', 'hero_tagline')) {
                $table->string('hero_tagline')->nullable()->after('region');
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['region', 'hero_tagline']);
        });
    }
};
