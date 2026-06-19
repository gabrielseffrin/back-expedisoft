<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Garante a criação do admin sem dar erro se ele já existir
        User::firstOrCreate(
            ['email' => 'admin@expedisoft.com'], // Busca por este email
            [
                'name' => 'Gabriel Admin',
                'password' => bcrypt('Expedisoft@TCC'),
                'rule' => 'admin',
            ]
        );
    }
}
