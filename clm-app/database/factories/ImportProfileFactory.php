<?php

namespace Database\Factories;

use App\Models\ImportProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportProfileFactory extends Factory
{
    protected $model = ImportProfile::class;

    public function definition(): array
    {
        return [
            'name' => 'Profile '.fake()->unique()->word(),
            'table_name' => 'cases',
            'header_hash' => null,
            'is_active' => true,
            'settings_json' => null,
        ];
    }
}


