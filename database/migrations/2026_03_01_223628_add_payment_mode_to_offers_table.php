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
        Schema::table('offers', function (Blueprint $table) {
            if (! Schema::hasColumn('offers', 'payment_mode')) {
                $table->enum('payment_mode', ['online', 'on_site', 'both'])->default('both')->after('price');
            }
        });;
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }
};
