<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users =
            [
                [
                    'name' => 'Mohamed',
                    'email' => 'admin@admin.com',
                    'phone' => '01024328382',
                    'role' => 'admin',
                    'password' => bcrypt('admin'),
                    'slug' => 'mohamed-admin',
                ],
                [
                    'name' => 'Ahmed',
                    'email' => 'user@user.com',
                    'phone' => '01007352061',
                    'role' => 'user',
                    'password' => bcrypt('user'),
                    'slug' => 'ahmed-admin',
                ],

            ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}