<?php

namespace Database\Seeders;

use App\Models\User;
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
                    'name' => 'Mohamed - محمد',
                    'email' => 'admin@admin.com',
                    'phone' => '01024328382',
                    'role' => 'admin',
                    'password' => bcrypt('admin'),
                    'slug' => 'mohamed-admin',
                ],
                [
                    'name' => 'Ahmed - أحمد',
                    'email' => 'user@user.com',
                    'phone' => '01007352061',
                    'role' => 'user',
                    'password' => bcrypt('user'),
                    'slug' => 'ahmed-admin',
                ],

            ];

        foreach ($users as $user) {
            User::query()
                ->where('email', $user['email'])
                ->where('name', explode(' - ', $user['name'], 2)[0])
                ->update(['name' => $user['name']]);

            User::firstOrCreate(
                ['email' => $user['email']],
                $user + ['is_active' => true],
            );
        }
    }
}
