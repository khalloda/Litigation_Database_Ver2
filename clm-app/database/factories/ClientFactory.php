<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_name_ar' => $this->faker->name,
            'client_name_en' => $this->faker->name,
            'client_type' => $this->faker->randomElement(['Individual', 'Company', 'Government']),
            'contact_person' => $this->faker->name,
            'contact_email' => $this->faker->safeEmail,
            'contact_phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'notes' => $this->faker->sentence,
        ];
    }
}
