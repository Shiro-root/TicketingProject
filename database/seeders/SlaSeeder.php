<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Models\Sla;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    public function run(): void
    {
        $slas = [
            TicketPriority::CRITICAL->value => ['name' => 'SLA Critical', 'response' => 15, 'resolution' => 2 * 60],
            TicketPriority::HIGH->value => ['name' => 'SLA High', 'response' => 60, 'resolution' => 6 * 60],
            TicketPriority::MEDIUM->value => ['name' => 'SLA Medium', 'response' => 240, 'resolution' => 24 * 60],
            TicketPriority::LOW->value => ['name' => 'SLA Low', 'response' => 480, 'resolution' => 72 * 60],
        ];

        foreach ($slas as $priority => $data) {
            Sla::firstOrCreate(['priority' => $priority], [
                'name' => $data['name'],
                'response_time_minutes' => $data['response'],
                'resolution_time_minutes' => $data['resolution'],
                'is_active' => true,
            ]);
        }
    }
}
