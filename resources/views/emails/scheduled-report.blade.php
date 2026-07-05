@component('mail::message')
# Laporan Terjadwal: {{ $schedule->name }}

Halo,

Berikut ringkasan laporan ticket sesuai jadwal **{{ $schedule->frequency }}** yang Anda daftarkan.
File lengkap terlampir dalam format **{{ strtoupper($schedule->format) }}**.

@component('mail::table')
| Metrik | Nilai |
| :--- | ---: |
| Total Ticket | {{ $summary['total'] }} |
| Resolved / Closed | {{ $summary['resolved_total'] }} |
| Overdue Saat Ini | {{ $summary['overdue_total'] }} |
| SLA Compliance | {{ $summary['sla_percentage'] }}% |
| Rata-rata Waktu Resolusi | {{ $summary['avg_resolution_hours'] }} jam |
@endcomponent

@component('mail::button', ['url' => route('reports.index')])
Buka Halaman Report
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
