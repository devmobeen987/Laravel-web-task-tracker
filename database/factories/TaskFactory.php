<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Support\ArticleUrlNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $url = $this->faker->unique()->url();

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'article_url' => $url,
            'article_key' => ArticleUrlNormalizer::key($url),
            'site_label' => null,
            'type' => $this->faker->randomElement(['new', 'refurbished']),
            'auto_detected_type' => 'unknown',
            'status' => 'completed',
            'work_date' => now()->toDateString(),
            'time_entry_mode' => 'manual',
            'clock_in_at' => now()->startOfDay()->addHours(9),
            'clock_out_at' => now()->startOfDay()->addHours(17),
            'break_start_at' => null,
            'break_end_at' => null,
        ];
    }
}
