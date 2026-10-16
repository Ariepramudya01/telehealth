<?php

namespace Database\Factories;

use App\Enums\KategoriRisiko;
use App\Models\IbuHamil;
use App\Models\Skrining;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skrining>
 */
class SkriningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalSkor = fake()->randomElement([2, 6, 8, 10, 12, 14, 16]);

        return [
            'ibu_hamil_id' => IbuHamil::factory(),
            'user_id' => User::factory(),
            'tanggal_skrining' => fake()->dateTimeBetween('-6 months', 'now'),
            'usia_kehamilan_minggu' => fake()->numberBetween(4, 40),
            'trimester' => fake()->numberBetween(1, 3),
            'total_skor' => $totalSkor,
            'kategori_risiko' => KategoriRisiko::fromScore($totalSkor),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
