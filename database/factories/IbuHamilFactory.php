<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\IbuHamil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IbuHamil>
 */
class IbuHamilFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hpht = fake()->dateTimeBetween('-8 months', '-1 month');
        $taksiran = (clone $hpht)->modify('+280 days');

        return [
            'user_id' => User::factory(),
            'desa_id' => Desa::factory(),
            'nama' => fake()->name('female'),
            'nik' => fake()->unique()->numerify('################'),
            'tanggal_lahir' => fake()->dateTimeBetween('-45 years', '-17 years'),
            'alamat' => fake()->address(),
            'no_hp' => fake()->phoneNumber(),
            'nama_suami' => fake()->name('male'),
            'golongan_darah' => fake()->randomElement(['A', 'B', 'AB', 'O']),
            'tinggi_badan' => fake()->randomFloat(1, 140, 175),
            'berat_badan' => fake()->randomFloat(1, 45, 90),
            'hpht' => $hpht,
            'taksiran_persalinan' => $taksiran,
            'gravida' => fake()->numberBetween(1, 6),
            'para' => fake()->numberBetween(0, 5),
            'abortus' => fake()->numberBetween(0, 2),
            'jumlah_anak_hidup' => fake()->numberBetween(0, 5),
            'is_active' => true,
        ];
    }
}
