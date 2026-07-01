<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\TicketStatus;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = Ticket::factory()->count(80)->create();

        // A batch of guaranteed-overdue tickets so the Dashboard "Overdue" widget has data.
        $tickets = $tickets->merge(Ticket::factory()->overdue()->count(10)->create());

        $tagIds = Tag::pluck('id');
        $userIds = User::pluck('id');

        foreach ($tickets as $ticket) {
            // Random tags
            $ticket->tags()->attach($tagIds->random(min(2, $tagIds->count())));

            // Random watchers (people following the ticket)
            $ticket->watchers()->attach($userIds->random(min(2, $userIds->count())));

            // Timeline: created
            $ticket->activities()->create([
                'user_id' => $ticket->created_by,
                'type' => ActivityType::CREATED,
                'description' => 'membuat ticket #'.$ticket->ticket_number,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->created_at,
            ]);

            if ($ticket->assigned_to) {
                $ticket->activities()->create([
                    'user_id' => $ticket->creator?->id,
                    'type' => ActivityType::ASSIGNED,
                    'description' => 'menugaskan teknisi ke ticket ini',
                    'meta' => ['assigned_to' => $ticket->assigned_to],
                    'created_at' => $ticket->created_at->addMinutes(10),
                    'updated_at' => $ticket->created_at->addMinutes(10),
                ]);
            }

            if ($ticket->accepted_at) {
                $ticket->activities()->create([
                    'user_id' => $ticket->assigned_to,
                    'type' => ActivityType::ACCEPTED,
                    'description' => 'menerima ticket',
                    'created_at' => $ticket->accepted_at,
                    'updated_at' => $ticket->accepted_at,
                ]);
            }

            $ticket->activities()->create([
                'user_id' => $ticket->assigned_to ?? $ticket->created_by,
                'type' => ActivityType::STATUS_CHANGED,
                'description' => 'mengubah status menjadi '.$ticket->status->label(),
                'meta' => ['to' => $ticket->status->value],
                'created_at' => $ticket->updated_at,
                'updated_at' => $ticket->updated_at,
            ]);

            if ($ticket->status === TicketStatus::CLOSED && $ticket->isRated()) {
                $ticket->activities()->create([
                    'user_id' => $ticket->created_by,
                    'type' => ActivityType::RATED,
                    'description' => 'memberikan rating '.$ticket->rating.' bintang',
                    'created_at' => $ticket->closed_at,
                    'updated_at' => $ticket->closed_at,
                ]);
            }

            // A couple of comments per ticket, including one internal note.
            TicketComment::factory()->count(rand(1, 3))->create([
                'ticket_id' => $ticket->id,
            ]);

            if (rand(0, 1)) {
                TicketComment::factory()->internal()->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->assigned_to ?? $userIds->random(),
                ]);
            }

            // A couple of bookmarks for realism (used by the "Favorite Ticket" bonus feature).
            $ticket->bookmarkedBy()->attach($userIds->random(min(1, $userIds->count())));
        }
    }
}
