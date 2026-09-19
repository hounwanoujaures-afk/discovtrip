<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            if (! Schema::hasColumn('cities', 'region')) {
                $table->string('region')->nullable()->after('country_id');
            }
            if (! Schema::hasColumn('cities', 'category')) {
                $table->string('category')->nullable()->after('region');
            }
            if (! Schema::hasColumn('cities', 'distance_from_cotonou')) {
                $table->integer('distance_from_cotonou')->nullable()->after('category');
            }
            if (! Schema::hasColumn('cities', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('distance_from_cotonou');
            }
            if (! Schema::hasColumn('cities', 'best_season')) {
                $table->string('best_season')->nullable()->after('duration_days');
            }
            if (! Schema::hasColumn('cities', 'highlights')) {
                $table->json('highlights')->nullable()->after('best_season');
            }
            if (! Schema::hasColumn('cities', 'landmarks')) {
                $table->json('landmarks')->nullable()->after('highlights');
            }
            if (! Schema::hasColumn('cities', 'how_to_get_there')) {
                $table->text('how_to_get_there')->nullable()->after('landmarks');
            }
            if (! Schema::hasColumn('cities', 'best_time_detail')) {
                $table->text('best_time_detail')->nullable()->after('how_to_get_there');
            }
            if (! Schema::hasColumn('cities', 'budget_range')) {
                $table->string('budget_range')->nullable()->after('best_time_detail');
            }
            if (! Schema::hasColumn('cities', 'fun_facts')) {
                $table->json('fun_facts')->nullable()->after('budget_range');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn([
                'region', 'category', 'distance_from_cotonou', 'duration_days',
                'best_season', 'highlights', 'landmarks', 'how_to_get_there',
                'best_time_detail', 'budget_range', 'fun_facts',
            ]);
        });
    }
};
