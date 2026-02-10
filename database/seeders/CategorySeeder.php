<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private const DEFAULT_CATEGORIES = [
        'Moradia',
        'Água',
        'Luz',
        'Internet',
        'Mercado',
        'Transporte',
        'Saúde',
        'Cartão',
        'Assinaturas',
        'Educação',
        'Lazer',
    ];

    public function run(): void
    {
        User::query()->chunkById(200, function ($users): void {
            foreach ($users as $user) {
                foreach (self::DEFAULT_CATEGORIES as $name) {
                    Category::query()->firstOrCreate([
                        'user_id' => $user->id,
                        'name' => $name,
                    ]);
                }
            }
        });
    }
}
