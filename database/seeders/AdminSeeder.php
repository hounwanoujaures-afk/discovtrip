<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'admin@discovtrip.com')->delete();
        
        $user = new User();
        $user->first_name = 'Admin';
        $user->last_name = 'DiscovTrip';
        $user->email = 'admin@discovtrip.com';
        $user->forceFill([
            'password' => Hash::make('Admin123!'),
            'role' => 'admin',
            'is_active' => true,
            'is_banned' => false,
            'email_verified' => true,
        ])->save();
    }
}