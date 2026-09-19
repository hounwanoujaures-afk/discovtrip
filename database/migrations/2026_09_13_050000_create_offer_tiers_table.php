<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('offer_tiers')) {
            return;
        }

        Schema::create('offer_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['discovery', 'comfort', 'exception'])->default('discovery');
            $table->string('label')->nullable();
            $table->string('tagline')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('price_is_indicative')->default(false);
            $table->string('currency', 10)->default('XOF');
            $table->text('description')->nullable();
            $table->json('included_items')->nullable();
            $table->json('excluded_items')->nullable();
            $table->boolean('whatsapp_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('offer_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_tiers');
    }
};
