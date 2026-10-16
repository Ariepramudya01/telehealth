<?php

namespace Database\Factories;

use App\Models\Desa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Desa>
 */
class DesaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Desa '.fake()->unique()->city(),
            'kecamatan' => 'Kecamatan '.fake()->city(),
            'kabupaten' => 'Kabupaten '.fake()->city(),
            'kode_desa' => fake()->unique()->numerify('##.##.##.####'),
        ];
    }
}
