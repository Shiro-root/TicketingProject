<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@helpdesk.test')->first();

        Announcement::firstOrCreate(
            ['title' => 'Selamat Datang di Helpdesk System'],
            [
                'content' => 'Sistem helpdesk baru telah aktif. Silakan buat ticket untuk setiap kendala IT, HR, atau operasional.',
                'type' => 'info',
                'is_active' => true,
                'created_by' => $admin?->id,
            ]
        );

        Announcement::firstOrCreate(
            ['title' => 'Jadwal Maintenance Server'],
            [
                'content' => 'Maintenance server akan dilakukan setiap Minggu pukul 23:00 - 01:00 WIB. Layanan mungkin tidak dapat diakses sementara.',
                'type' => 'warning',
                'is_active' => true,
                'created_by' => $admin?->id,
            ]
        );
    }
}
