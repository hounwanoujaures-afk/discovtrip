<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'participants')) {
                $table->unsignedInteger('participants')->default(1)->after('infants');
            }
            if (! Schema::hasColumn('bookings', 'booking_time')) {
                $table->string('booking_time', 10)->nullable()->after('booking_date');
            }
            if (! Schema::hasColumn('bookings', 'notes')) {
                $table->text('notes')->nullable()->after('special_requests');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['participants', 'booking_time', 'notes'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
