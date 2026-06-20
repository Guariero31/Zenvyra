<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\IdentidadeVisual;
use Illuminate\Database\Seeder;

class IdentidadeVisualSeeder extends Seeder
{
    public function run(): void
    {
        IdentidadeVisual::firstOrCreate([], IdentidadeVisual::defaults());
    }
}
