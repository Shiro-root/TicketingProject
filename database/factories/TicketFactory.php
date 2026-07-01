<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\Sla;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketFactory extends Factory
{
    protected static int $sequence = 1;

    public function definition(): array
    {
        $priority = fake()->randomElement(TicketPriority::cases());
        $status = fake()->randomElement(TicketStatus::cases());
        $createdAt = fake()->dateTimeBetween('-3 months', 'now');
        $sla = Sla::where('priority', $priority->value)->first();
        $slaMinutes = $sla?->resolution_time_minutes ?? $priority->defaultSlaHours() * 60;

        $isDone = in_array($status, [TicketStatus::RESOLVED, TicketStatus::CLOSED], true);

        return [
            'ticket_number' => 'TCK-'.date('Y', $createdAt->getTimestamp()).'-'.str_pad((string) static::$sequence++, 6, '0', STR_PAD_LEFT),
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraphs(3, true),
            'category_id' => Category::query()->inRandomOrder()->value('id'),
            'department_id' => Department::query()->inRandomOrder()->value('id'),
            'priority' => $priority->value,
            'status' => $status->value,
            'sla_id' => $sla?->id,
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'assigned_to' => $status !== TicketStatus::OPEN ? User::query()->inRandomOrder()->value('id') : null,
            'due_at' => Carbon::instance($createdAt)->addMinutes($slaMinutes),
            'first_response_at' => $status !== TicketStatus::OPEN ? Carbon::instance($createdAt)->addMinutes(fake()->numberBetween(5, 120)) : null,
            'accepted_at' => in_array($status, [TicketStatus::ACCEPTED, TicketStatus::IN_PROGRESS, TicketStatus::WAITING_USER, TicketStatus::PENDING_VENDOR, TicketStatus::RESOLVED, TicketStatus::CLOSED], true)
                ? Carbon::instance($createdAt)->addMinutes(fake()->numberBetween(10, 180)) : null,
            'resolved_at' => $isDone ? Carbon::instance($createdAt)->addHours(fake()->numberBetween(1, 48)) : null,
            'closed_at' => $status === TicketStatus::CLOSED ? Carbon::instance($createdAt)->addHours(fake()->numberBetween(49, 96)) : null,
            'is_sla_breached' => ! $isDone && Carbon::instance($createdAt)->addMinutes($slaMinutes)->isPast(),
            'is_archived' => false,
            'rating' => $status === TicketStatus::CLOSED ? fake()->optional(0.7)->numberBetween(1, 5) : null,
            'feedback' => $status === TicketStatus::CLOSED ? fake()->optional(0.4)->sentence() : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::IN_PROGRESS->value,
            'due_at' => now()->subDays(fake()->numberBetween(1, 5)),
            'is_sla_breached' => true,
        ]);
    }
}
