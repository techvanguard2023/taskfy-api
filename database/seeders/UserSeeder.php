<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Admin Bot',
            'email' => 'adminbot@taskfy.com.br',
            'phone' => '+5521989119661',
            'password' => 'Rm@150917',
            'role' => 'admin'
        ]);

    }
}
