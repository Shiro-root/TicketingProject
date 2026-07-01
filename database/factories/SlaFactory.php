<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlaFactory extends Factory
{
    public function definition(): array
    {
        $priority = fake()->randomElement(TicketPriority::cases());

        return [
            'name' => 'SLA '.$priority->label(),
            'priority' => $priority->value,
            'response_time_minutes' => match ($priority) {
                TicketPriority::CRITICAL => 15,
                TicketPriority::HIGH => 60,
                TicketPriority::MEDIUM => 240,
                TicketPriority::LOW => 480,
            },
            'resolution_time_minutes' => $priority->defaultSlaHours() * 60,
            'is_active' => true,
        ];
    }
}
