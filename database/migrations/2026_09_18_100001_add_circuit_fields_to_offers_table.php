<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('is_circuit')->default(false)->after('category');
            $table->unsignedTinyInteger('duration_days')->nullable()->after('duration_minutes');
            $table->boolean('transport_from_cotonou_included')->default(false)->after('included_items');
            $table->json('supplements')->nullable()->after('excluded_items');
            $table->boolean('is_special_event')->default(false)->after('is_featured');
            $table->date('valid_from')->nullable()->after('is_special_event');
            $table->date('valid_until')->nullable()->after('valid_from');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'is_circuit',
                'duration_days',
                'transport_from_cotonou_included',
                'supplements',
                'is_special_event',
                'valid_from',
                'valid_until',
            ]);
        });
    }
};
