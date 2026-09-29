<?php

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => fake()->company(),
            'bio' => fake()->paragraph(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'status' => 'pending',
            'rejection_reason' => null,
            'reviewed_at' => null,
        ];
    }

    /** Application is still pending review. */
    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'rejection_reason' => null,
            'reviewed_at' => null,
        ]);
    }

    /** Application was approved; caller should also sync the user role. */
    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'rejection_reason' => null,
            'reviewed_at' => now(),
        ]);
    }

    /** Application was rejected with a reason. */
    public function rejected(): static
    {
        return $this->state([
            'status' => 'rejected',
            'rejection_reason' => 'Does not meet requirements.',
            'reviewed_at' => now(),
        ]);
    }
}
