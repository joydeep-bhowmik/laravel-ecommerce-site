<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'John Doe',
                'email'    => 'john@example.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Jane Smith',
                'email'    => 'jane@example.com',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ],
            [
                'name'     => 'Alice Brown',
                'email'    => 'alice@example.com',
                'password' => Hash::make('password'),
                'role'     => 'user',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
