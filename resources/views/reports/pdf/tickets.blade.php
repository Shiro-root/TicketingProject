<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Ticket</title>
    <style>
        /* dompdf tidak mendukung Tailwind — CSS ditulis manual mengikuti token warna DESIGN.md */
        @page { margin: 24px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #211922; }
        h1 { font-size: 18px; color: #000000; margin: 0 0 4px; }
        p.subtitle { color: #62625b; margin: 0 0 16px; font-size: 10px; }

        .meta { margin-bottom: 16px; }
        .meta td { padding: 2px 8px 2px 0; font-size: 10px; color: #33332e; }
        .meta td.label { color: #91918c; width: 110px; }

        .summary { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .summary td {
            width: 25%; padding: 10px; border: 1px solid #dadad3; text-align: center;
        }
        .summary .value { font-size: 16px; font-weight: bold; color: #000000; display: block; }
        .summary .caption { font-size: 9px; color: #62625b; display: block; margin-top: 2px; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            background-color: #262622; color: #ffffff; text-align: left;
            padding: 6px 6px; font-size: 9px; text-transform: uppercase;
        }
        table.data td {
            padding: 5px 6px; border-bottom: 1px solid #e5e5e0; font-size: 9px; vertical-align: top;
        }
        table.data tr:nth-child(even) { background-color: #f6f6f3; }

        .badge { padding: 2px 6px; border-radius: 8px; font-size: 8px; color: #ffffff; }
        .badge-red { background-color: #9e0a0a; }
        .badge-gray { background-color: #91918c; }

        .footer { margin-top: 16px; font-size: 8px; color: #91918c; text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Ticket — Helpdesk Enterprise</h1>
    <p class="subtitle">Dibuat oleh {{ $generatedBy->name }} pada {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB</p>

    <table class="meta">
        <tr>
            <td class="label">Periode</td>
            <td>{{ $filters['date_from'] ?? '—' }} s/d {{ $filters['date_to'] ?? '—' }}</td>
            <td class="label">Total Data</td>
            <td>{{ $tickets->count() }} ticket</td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td><span class="value">{{ $summary['total'] }}</span><span class="caption">Total Ticket</span></td>
            <td><span class="value">{{ $summary['resolved_total'] }}</span><span class="caption">Resolved / Closed</span></td>
            <td><span class="value">{{ $summary['overdue_total'] }}</span><span class="caption">Overdue Saat Ini</span></td>
            <td><span class="value">{{ $summary['sla_percentage'] }}%</span><span class="caption">SLA Compliance</span></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No. Ticket</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Department</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Dibuat oleh</th>
                <th>Teknisi</th>
                <th>Dibuat</th>
                <th>Due</th>
                <th>SLA</th>
                <th>Rating</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 40) }}</td>
                    <td>{{ $ticket->category->name ?? '-' }}</td>
                    <td>{{ $ticket->department->name ?? '-' }}</td>
                    <td>{{ $ticket->priority->label() }}</td>
                    <td>{{ $ticket->status->label() }}</td>
                    <td>{{ $ticket->creator->name ?? '-' }}</td>
                    <td>{{ $ticket->assignee->name ?? '-' }}</td>
                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $ticket->due_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>
                        @if($ticket->is_sla_breached)
                            <span class="badge badge-red">Terlewati</span>
                        @else
                            <span class="badge badge-gray">Aman</span>
                        @endif
                    </td>
                    <td>{{ $ticket->rating ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="12" style="text-align:center;">Tidak ada data untuk filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Helpdesk Enterprise — Laporan ini dibuat otomatis oleh sistem.</p>
</body>
</html>
