<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\In;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();
        // create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@cress.com',
            'phone' => '0987654321',
            'sex' => 'male',
            'role' => 'admin',
            'address' => fake()->optional()->streetAddress(),
            'town' => 'Lusaka',
            'email_verified_at' => now(),
            'password' => Hash::make('#password'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null
        ]);

        User::factory(39)->create();
        (new IncidentSeeder)->run();
        (new CenterSeeder)->run();
    }
}
