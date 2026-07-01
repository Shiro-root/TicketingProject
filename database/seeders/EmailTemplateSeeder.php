<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'ticket_created',
                'name' => 'Ticket Created',
                'subject' => '[{{ticket_number}}] Ticket Anda telah dibuat',
                'body_html' => '<p>Halo {{user_name}},</p><p>Ticket <strong>{{ticket_number}}</strong> - "{{subject}}" telah berhasil dibuat dan akan segera ditindaklanjuti.</p>',
            ],
            [
                'key' => 'ticket_assigned',
                'name' => 'Ticket Assigned',
                'subject' => '[{{ticket_number}}] Ticket ditugaskan kepada Anda',
                'body_html' => '<p>Halo {{technician_name}},</p><p>Anda ditugaskan untuk menangani ticket <strong>{{ticket_number}}</strong> - "{{subject}}".</p>',
            ],
            [
                'key' => 'status_changed',
                'name' => 'Status Changed',
                'subject' => '[{{ticket_number}}] Status ticket berubah menjadi {{status}}',
                'body_html' => '<p>Halo {{user_name}},</p><p>Status ticket <strong>{{ticket_number}}</strong> kini menjadi <strong>{{status}}</strong>.</p>',
            ],
            [
                'key' => 'new_comment',
                'name' => 'New Comment',
                'subject' => '[{{ticket_number}}] Komentar baru pada ticket Anda',
                'body_html' => '<p>{{commenter_name}} menambahkan komentar pada ticket <strong>{{ticket_number}}</strong>:</p><blockquote>{{comment_body}}</blockquote>',
            ],
            [
                'key' => 'sla_warning',
                'name' => 'SLA Warning',
                'subject' => '[{{ticket_number}}] SLA hampir habis',
                'body_html' => '<p>Ticket <strong>{{ticket_number}}</strong> akan melewati batas SLA dalam {{time_remaining}}. Mohon segera ditindaklanjuti.</p>',
            ],
            [
                'key' => 'sla_breached',
                'name' => 'SLA Breached',
                'subject' => '[{{ticket_number}}] SLA telah terlewati',
                'body_html' => '<p>Ticket <strong>{{ticket_number}}</strong> telah melewati batas SLA yang ditentukan. Eskalasi otomatis telah dilakukan.</p>',
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::firstOrCreate(['key' => $template['key']], $template + ['is_active' => true]);
        }
    }
}
