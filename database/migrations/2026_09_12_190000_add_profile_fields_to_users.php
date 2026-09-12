<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('nationality');
            }
            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 5)->nullable()->after('bio');
            }
            if (! Schema::hasColumn('users', 'birthday')) {
                $table->date('birthday')->nullable()->after('locale');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('birthday');
            }
            if (! Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'locale', 'birthday', 'gender', 'profile_picture']);
        });
    }
};
