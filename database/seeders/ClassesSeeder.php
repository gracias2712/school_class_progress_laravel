<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['class_name' => 'XI RPL 1'],  // ID: 1
            ['class_name' => 'XI RPL 2'],  // ID: 2
            ['class_name' => 'XII RPL 1'], // ID: 3
            ['class_name' => 'XII RPL 2'], // ID: 4
        ];

        foreach ($classes as $classe) {
            Classe::firstOrCreate($classe);
        }
    }
}