<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Utilisé par SendBookingReminders pour ne pas renvoyer deux fois le rappel
            $table->boolean('reminder_sent')->default(false)->after('notes');
            $table->timestamp('reminder_sent_at')->nullable()->after('reminder_sent');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent', 'reminder_sent_at']);
        });
    }
};
