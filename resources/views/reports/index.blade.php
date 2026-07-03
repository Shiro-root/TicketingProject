@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between flex-wrap gap-md">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Report</h1>
            <p class="text-body-md text-mute">Laporan ticket — filter, lihat ringkasan, lalu export ke PDF atau Excel.</p>
        </div>
        @if(auth()->user()->hasPermission('report.export'))
            <div class="flex items-center gap-sm">
                <a href="{{ route('reports.export.pdf', $filters) }}" class="btn-secondary">📄 Export PDF</a>
                <a href="{{ route('reports.export.excel', $filters) }}" class="btn-primary">📊 Export Excel</a>
            </div>
        @endif
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('reports.index') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg flex flex-wrap items-end gap-md">
        <div>
            <label class="field-label">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="field-input">
        </div>
        <div>
            <label class="field-label">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="field-input">
        </div>
        <div>
            <label class="field-label">Department</label>
            <select name="department_id" class="field-input">
                <option value="">Semua</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Kategori</label>
            <select name="category_id" class="field-input">
                <option value="">Semua</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Teknisi</label>
            <select name="assigned_to" class="field-input">
                <option value="">Semua</option>
                @foreach ($technicians as $tech)
                    <option value="{{ $tech->id }}" @selected((string) ($filters['assigned_to'] ?? '') === (string) $tech->id)>{{ $tech->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Status</label>
            <select name="status" class="field-input">
                <option value="">Semua</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Prioritas</label>
            <select name="priority" class="field-input">
                <option value="">Semua</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-sm">
            <button type="submit" class="btn-primary">Terapkan</button>
            <a href="{{ route('reports.index') }}" class="btn-tertiary">Reset</a>
        </div>
    </form>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-lg">
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg">
            <p class="text-body-sm text-mute mb-xxs">Total Ticket</p>
            <p class="text-heading-xl text-ink dark:text-on-dark">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg">
            <p class="text-body-sm text-mute mb-xxs">Resolved / Closed</p>
            <p class="text-heading-xl text-ink dark:text-on-dark">{{ $summary['resolved_total'] }}</p>
        </div>
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg">
            <p class="text-body-sm text-mute mb-xxs">Overdue Saat Ini</p>
            <p class="text-heading-xl text-error">{{ $summary['overdue_total'] }}</p>
        </div>
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg">
            <p class="text-body-sm text-mute mb-xxs">SLA Compliance</p>
            <p class="text-heading-xl text-ink dark:text-on-dark">{{ $summary['sla_percentage'] }}%</p>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket per Status</h2>
            <canvas id="chartStatus" height="220"></canvas>
        </div>
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket per Prioritas</h2>
            <canvas id="chartPriority" height="220"></canvas>
        </div>
    </div>

    {{-- Kinerja teknisi --}}
    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
        <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Kinerja Teknisi</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-body-sm">
                <thead>
                    <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                        <th class="px-lg py-md font-semibold">Teknisi</th>
                        <th class="px-lg py-md font-semibold">Ditugaskan</th>
                        <th class="px-lg py-md font-semibold">Selesai</th>
                        <th class="px-lg py-md font-semibold">Rata-rata Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($technicianPerformance as $tech)
                        <tr class="border-b border-hairline dark:border-white/10 last:border-0">
                            <td class="px-lg py-md text-body-strong text-ink dark:text-on-dark">{{ $tech->name }}</td>
                            <td class="px-lg py-md text-mute">{{ $tech->assigned_count }}</td>
                            <td class="px-lg py-md text-mute">{{ $tech->resolved_count }}</td>
                            <td class="px-lg py-md text-mute">⭐ {{ $tech->avg_rating ? number_format($tech->avg_rating, 1) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-lg py-xl text-center text-mute">Tidak ada data teknisi untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Preview tabel ticket (maks 50 baris — gunakan Export untuk data lengkap) --}}
    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <div class="px-lg py-md border-b border-hairline dark:border-white/10 flex items-center justify-between">
            <h2 class="text-heading-md text-ink dark:text-on-dark">Preview Ticket</h2>
            <span class="text-caption-sm text-mute">Menampilkan maks. 50 baris terbaru — gunakan Export untuk data lengkap</span>
        </div>
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">No. Ticket</th>
                    <th class="px-lg py-md font-semibold">Judul</th>
                    <th class="px-lg py-md font-semibold">Kategori</th>
                    <th class="px-lg py-md font-semibold">Prioritas</th>
                    <th class="px-lg py-md font-semibold">Status</th>
                    <th class="px-lg py-md font-semibold">Teknisi</th>
                    <th class="px-lg py-md font-semibold">Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0 hover:bg-surface-card dark:hover:bg-white/5">
                        <td class="px-lg py-md">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-body-strong text-ink dark:text-on-dark hover:underline">{{ $ticket->ticket_number }}</a>
                        </td>
                        <td class="px-lg py-md text-ink dark:text-on-dark truncate max-w-[240px]">{{ $ticket->subject }}</td>
                        <td class="px-lg py-md text-mute">{{ $ticket->category->name ?? '—' }}</td>
                        <td class="px-lg py-md"><x-priority-badge :label="$ticket->priority->label()" :color="$ticket->priority->color()" /></td>
                        <td class="px-lg py-md"><x-status-badge :label="$ticket->status->label()" :color="$ticket->status->color()" /></td>
                        <td class="px-lg py-md text-mute">{{ $ticket->assignee->name ?? '—' }}</td>
                        <td class="px-lg py-md text-mute">{{ $ticket->created_at->translatedFormat('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-lg py-xxl text-center text-mute">Tidak ada ticket untuk filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
    const reportPalette = { blue: '#435ee5', purple: '#7e238b', indigo: '#617bff', amber: '#e6a700', orange: '#e67a00', green: '#103c25', gray: '#91918c', red: '#e60023' };

    const statusColors = @json(collect($summary['by_status'])->pluck('color'));
    const priorityColors = @json(collect($summary['by_priority'])->pluck('color'));

    new Chart(document.getElementById('chartStatus'), {
        type: 'bar',
        data: {
            labels: @json(collect($summary['by_status'])->pluck('label')),
            datasets: [{
                label: 'Ticket',
                data: @json(collect($summary['by_status'])->pluck('total')),
                backgroundColor: statusColors.map(c => reportPalette[c] || '#91918c'),
            }],
        },
        options: { plugins: { legend: { display: false } } },
    });

    new Chart(document.getElementById('chartPriority'), {
        type: 'doughnut',
        data: {
            labels: @json(collect($summary['by_priority'])->pluck('label')),
            datasets: [{
                data: @json(collect($summary['by_priority'])->pluck('total')),
                backgroundColor: priorityColors.map(c => reportPalette[c] || '#91918c'),
            }],
        },
    });
</script>
@endpush
@endsection
