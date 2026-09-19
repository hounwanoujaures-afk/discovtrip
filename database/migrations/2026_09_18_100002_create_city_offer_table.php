<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_offer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('visit_order')->default(1);
            $table->timestamps();

            $table->unique(['offer_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_offer');
    }
};
