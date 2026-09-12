<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration countries 2026 — rendue idempotente.
 * La table est créée par 0000_00_00_000000_create_all_base_tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('countries')) {
            return; // déjà créée par la migration consolidée
        }

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('code', 2)->nullable();
            $table->string('continent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
