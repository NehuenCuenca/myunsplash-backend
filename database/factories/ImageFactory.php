<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstNameMale,
            'url' => 'https://res.cloudinary.com/de9d1foso/image/upload/v1646511497/samples/landscapes/nature-mountains.jpg',
            'public_id' => 'fake_image',
        ];
    }
}
