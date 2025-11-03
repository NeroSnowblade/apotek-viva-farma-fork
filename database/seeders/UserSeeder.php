<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Import model User
use Illuminate\Support\Facades\Hash; // Import Hash facade

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data Pengguna Admin
        User::create([
            'namaUser' => 'Admin Apotek',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'level' => 'admin',
        ]);

        // Data Pengguna Apoteker
        User::create([
            'namaUser' => 'Apoteker Handal',
            'username' => 'apoteker',
            'password' => Hash::make('password123'),
            'level' => 'apoteker',
        ]);

        // Data Pengguna Kasir
        User::create([
            'namaUser' => 'Kasir Cepat',
            'username' => 'kasir',
            'password' => Hash::make('password123'),
            'level' => 'kasir',
        ]);
    }
}
