<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        $status = fake()->randomElement([Bill::STATUS_OPEN, Bill::STATUS_PAID]);

        return [
            'user_id' => User::factory(),
            'category_id' => function (array $attributes) {
                return Category::factory()->for(User::find($attributes['user_id']))->create()->id;
            },
            'title' => fake()->sentence(3),
            'amount_cents' => fake()->numberBetween(1_000, 150_000),
            'due_date' => fake()->dateTimeBetween('-1 month', '+2 months')->format('Y-m-d'),
            'status' => $status,
            'paid_at' => $status === Bill::STATUS_PAID ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => Bill::STATUS_OPEN,
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => Bill::STATUS_PAID,
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
