<?php

namespace Database\Factories;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aduan_no' => Complaint::generateAduanNo(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'officer_id' => null,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'location' => fake()->address(),
            'status' => fake()->randomElement(ComplaintStatus::cases()),
            'priority' => fake()->randomElement(ComplaintPriority::cases()),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => ComplaintStatus::Pending]);
    }

    public function resolved(): static
    {
        return $this->state(['status' => ComplaintStatus::Resolved]);
    }
}
