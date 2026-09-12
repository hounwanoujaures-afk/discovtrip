<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (! Schema::hasColumn('offers', 'short_description')) {
                $table->string('short_description')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('offers', 'category')) {
                $table->string('category')->nullable()->after('short_description');
            }
            if (! Schema::hasColumn('offers', 'guide_type')) {
                $table->string('guide_type')->default('agency')->after('user_id');
            }
            if (! Schema::hasColumn('offers', 'base_price')) {
                $table->decimal('base_price', 10, 2)->nullable()->after('price');
            }
            if (! Schema::hasColumn('offers', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('duration_hours');
            }
            if (! Schema::hasColumn('offers', 'languages')) {
                $table->json('languages')->nullable()->after('itinerary');
            }
            if (! Schema::hasColumn('offers', 'meeting_point')) {
                $table->string('meeting_point')->nullable()->after('languages');
            }
            if (! Schema::hasColumn('offers', 'faq')) {
                $table->json('faq')->nullable()->after('faqs');
            }
            if (! Schema::hasColumn('offers', 'video_url')) {
                $table->string('video_url')->nullable()->after('guide_video_url');
            }
            if (! Schema::hasColumn('offers', 'views_count')) {
                $table->integer('views_count')->default(0)->after('reviews_count');
            }
            if (! Schema::hasColumn('offers', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('views_count');
            }
            if (! Schema::hasColumn('offers', 'promotional_price')) {
                $table->decimal('promotional_price', 10, 2)->nullable()->after('base_price');
            }
            if (! Schema::hasColumn('offers', 'promotion_starts_at')) {
                $table->timestamp('promotion_starts_at')->nullable()->after('promotional_price');
            }
            if (! Schema::hasColumn('offers', 'promotion_ends_at')) {
                $table->timestamp('promotion_ends_at')->nullable()->after('promotion_starts_at');
            }
        });

        // Migration des données depuis les anciennes colonnes
        DB::statement('UPDATE offers SET base_price = price WHERE base_price IS NULL');
        DB::statement('UPDATE offers SET duration_minutes = duration_hours * 60 WHERE duration_minutes IS NULL AND duration_hours IS NOT NULL');
        DB::statement('UPDATE offers SET video_url = guide_video_url WHERE video_url IS NULL');
        DB::statement('UPDATE offers SET faq = faqs WHERE faq IS NULL');
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'short_description', 'category', 'guide_type', 'base_price',
                'duration_minutes', 'languages', 'meeting_point', 'faq',
                'video_url', 'views_count', 'sort_order', 'promotional_price',
                'promotion_starts_at', 'promotion_ends_at',
            ]);
        });
    }
};
