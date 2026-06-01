<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $types = ['like', 'like_post', 'comment', 'mention', 'friend_request', 'follow'];

        return [
            // Mấy cái ID này tí nữa bên Seeder mình sẽ truyền động vô sau nhen Pro
            'user_id' => null,
            'actor_id' => null,
            'type' => $this->faker->randomElement($types),
            'reference_id' => $this->faker->numberBetween(1, 20),
            'is_read' => $this->faker->boolean(30), // 30% đã đọc
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'updated_at' => now(),
        ];
    }
}