<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Instance;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'mobile' => '15881551001',
            'email' => 'fengqiyue@gmail.com',
            'password' => argon('111111'),
            'avatar' => '/avatars/1.png',
        ]);

        User::factory()->unverified()->create([
            'name' => 'user_'.Str::random(10),
            'mobile' => '18668097379',
            'password' => argon('111111'),
            'avatar' => '/avatars/2.png',
        ]);
    }
}
