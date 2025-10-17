<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(20)->create();

        Admin::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin9.com',
            'password' => argon('111111'),
        ]);

        User::factory()->create([
            'name' => 'fengqiyue',
            'email' => 'fengqiyue@gmail.com',
            'password' => argon('111111'),
        ]);

        User::factory()->unverified()->create([
            'password' => argon('111111'),
        ]);
    }
}
