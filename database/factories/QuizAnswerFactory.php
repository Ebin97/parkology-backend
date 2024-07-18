<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QuizAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        return [
            'title' => [
                'en' => fake()->text(50),
                'ar' => "الجواب رقم " . Str::random(30) . "-" . fake()->text(50),
            ],
            'slug' => (fake()->slug(50)),
            'correct' => fake()->randomNumber([0, 1]),
            'orders' => fake()->randomNumber([1, 2, 3, 4]),
            'quiz_id' => Quiz::query()->get()->random()->id

        ];
    }
}
