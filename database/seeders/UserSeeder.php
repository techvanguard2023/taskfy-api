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
            'password' => 'Rm@150917'
        ]);

        User::create([
            'name' => 'Robson Pedreira',
            'email' => 'masterdba6@gmail.com',
            'phone' => '+5521981321890',
            'password' => 'Rm@150917'
        ]);

        User::create([
            'name' => 'Usuário 2',
            'email' => 'usuario2@gmail.com',
            'phone' => '+5521999999999',
            'password' => 'Rm@150917'
        ]);

        User::create([
            'name' => 'Usuário 3',
            'email' => 'usuario3@gmail.com',
            'phone' => '+5521988888888',
            'password' => 'Rm@150917'
        ]);

        User::create([
            'name' => 'Usuário 4',
            'email' => 'usuario4@gmail.com',
            'phone' => '+5521977777777',
            'password' => 'Rm@150917'
        ]);
    }
}
