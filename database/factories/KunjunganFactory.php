<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\Kunjungan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kunjungan>
 */
class KunjunganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hadir = fake()->boolean(80);

        return [
            'ibu_hamil_id' => IbuHamil::factory(),
            'user_id' => User::factory(),
            'desa_id' => Desa::factory(),
            'tanggal_kunjungan' => fake()->dateTimeBetween('-6 months', 'now'),
            'jenis_kunjungan' => fake()->randomElement(['K1', 'K2', 'K3', 'K4', 'K5', 'K6']),
            'status_hadir' => $hadir,
            'status_dilayani' => $hadir ? fake()->boolean(90) : false,
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
