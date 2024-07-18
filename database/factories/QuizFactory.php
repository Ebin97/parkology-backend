<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
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
                'ar' => "السؤال رقم " . Str::random(30),
            ],
            'slug' => (fake()->slug(50)),
            'active' => 1,
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addDays(7),

        ];
    }
}
