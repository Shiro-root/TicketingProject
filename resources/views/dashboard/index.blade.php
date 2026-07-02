@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl" x-data="{}">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Dashboard</h1>
        <p class="text-body-md text-mute">Ringkasan operasional helpdesk — {{ now()->translatedFormat('l, d F Y') }}.</p>
    </div>

    {{-- Kartu status --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-lg">
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg">
            <p class="text-body-sm text-mute mb-xxs">Total Ticket</p>
            <p class="text-heading-xl text-ink dark:text-on-dark">{{ $statusCounts['total'] }}</p>
        </div>
        @foreach ($statusCounts['items'] as $item)
            <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg">
                <p class="text-body-sm text-mute mb-xxs">{{ $item['label'] }}</p>
                <p class="text-heading-xl text-ink dark:text-on-dark">{{ $item['total'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Statistik periode --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-lg">
        @foreach ([
            'Hari Ini' => $periodStats['today'],
            'Minggu Ini' => $periodStats['this_week'],
            'Bulan Ini' => $periodStats['this_month'],
            'Tahun Ini' => $periodStats['this_year'],
        ] as $label => $value)
            <div class="bg-surface-card dark:bg-white/5 rounded-md p-lg">
                <p class="text-body-sm text-mute mb-xxs">Ticket {{ $label }}</p>
                <p class="text-heading-lg text-ink dark:text-on-dark">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket per Bulan</h2>
            <canvas id="chartMonthly" height="220"></canvas>
        </div>
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket per Prioritas</h2>
            <canvas id="chartPriority" height="220"></canvas>
        </div>
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket per Kategori</h2>
            <canvas id="chartCategory" height="220"></canvas>
        </div>
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket per Teknisi</h2>
            <canvas id="chartTechnician" height="220"></canvas>
        </div>
    </div>

    {{-- Widget: Teknisi terbaik & SLA --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Teknisi Terbaik</h2>
            <div class="flex flex-col gap-md">
                @forelse ($bestTechnicians as $tech)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-sm">
                            <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-caption-md font-semibold">
                                {{ $tech->initials() }}
                            </span>
                            <span class="text-body-strong text-ink dark:text-on-dark">{{ $tech->name }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-body-sm text-ink dark:text-on-dark">{{ $tech->resolved_count }} selesai</p>
                            <p class="text-caption-sm text-mute">⭐ {{ $tech->avg_rating ? number_format($tech->avg_rating, 1) : '—' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-body-sm text-mute">Belum ada data.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">SLA Performance</h2>
            <div class="flex items-end gap-sm mb-md">
                <span class="text-heading-xl text-ink dark:text-on-dark">{{ $slaPerformance['percentage'] }}%</span>
                <span class="text-body-sm text-mute mb-xxs">tiket selesai sesuai SLA</span>
            </div>
            <div class="w-full h-2 rounded-full bg-surface-card dark:bg-white/10 overflow-hidden mb-lg">
                <div class="h-full bg-primary" style="width: {{ $slaPerformance['percentage'] }}%"></div>
            </div>
            <div class="grid grid-cols-2 gap-md text-body-sm">
                <p class="text-mute">Memenuhi SLA <span class="text-ink dark:text-on-dark font-semibold">{{ $slaPerformance['met_sla'] }}</span></p>
                <p class="text-mute">Melewati SLA <span class="text-error font-semibold">{{ $slaPerformance['breached'] }}</span></p>
                <p class="text-mute col-span-2">Overdue saat ini: <span class="text-error font-semibold">{{ $slaPerformance['currently_overdue'] }}</span> tiket</p>
            </div>
        </div>
    </div>

    {{-- Widget: Overdue, Ticket terbaru, Aktivitas terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket Overdue</h2>
            <div class="flex flex-col gap-md">
                @forelse ($overdueTickets as $ticket)
                    <div class="border-b border-hairline dark:border-white/10 pb-md last:border-0 last:pb-0">
                        <p class="text-body-strong text-error">{{ $ticket->ticket_number }}</p>
                        <p class="text-body-sm text-ink dark:text-on-dark truncate">{{ $ticket->subject }}</p>
                        <p class="text-caption-sm text-mute">Due: {{ $ticket->due_at?->translatedFormat('d M Y H:i') }}</p>
                    </div>
                @empty
                    <p class="text-body-sm text-mute">Tidak ada ticket overdue 🎉</p>
                @endforelse
            </div>
        </div>

        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket Terbaru</h2>
            <div class="flex flex-col gap-md">
                @forelse ($latestTickets as $ticket)
                    <div class="border-b border-hairline dark:border-white/10 pb-md last:border-0 last:pb-0">
                        <div class="flex items-center justify-between mb-xxs">
                            <p class="text-body-strong text-ink dark:text-on-dark">{{ $ticket->ticket_number }}</p>
                            <x-status-badge :label="$ticket->status->label()" :color="$ticket->status->color()" />
                        </div>
                        <p class="text-body-sm text-mute truncate">{{ $ticket->subject }}</p>
                    </div>
                @empty
                    <p class="text-body-sm text-mute">Belum ada ticket.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Aktivitas Terbaru</h2>
            <div class="flex flex-col gap-md">
                @forelse ($latestActivities as $activity)
                    <div class="border-b border-hairline dark:border-white/10 pb-md last:border-0 last:pb-0">
                        <p class="text-body-sm text-ink dark:text-on-dark">
                            <span class="text-body-strong">{{ $activity->user?->name ?? 'Sistem' }}</span>
                            {{ $activity->description }}
                        </p>
                        <p class="text-caption-sm text-mute">{{ $activity->ticket?->ticket_number }} · {{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-body-sm text-mute">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Manager+ only --}}
    @if ($managerStats)
        <div class="bg-surface-dark rounded-md p-xl text-on-dark">
            <h2 class="text-heading-md mb-lg">Performa Divisi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-xl">
                <div>
                    <p class="text-on-dark-mute text-body-sm mb-xxs">Rata-rata Waktu Penyelesaian</p>
                    <p class="text-heading-lg">{{ $managerStats['avg_resolution_hours'] }} jam</p>
                </div>
                <div>
                    <p class="text-on-dark-mute text-body-sm mb-md">Ticket per Divisi</p>
                    <div class="flex flex-col gap-xs">
                        @foreach ($managerStats['by_department'] as $dept)
                            <div class="flex items-center justify-between text-body-sm">
                                <span>{{ $dept['label'] }}</span>
                                <span class="font-semibold">{{ $dept['total'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
    const palette = ['#e60023', '#435ee5', '#7e238b', '#617bff', '#103c25', '#cc001f', '#91918c', '#262622'];

    new Chart(document.getElementById('chartMonthly'), {
        type: 'line',
        data: {
            labels: @json(collect($monthlyChart)->pluck('label')),
            datasets: [{
                label: 'Ticket',
                data: @json(collect($monthlyChart)->pluck('total')),
                borderColor: '#e60023',
                backgroundColor: 'rgba(230,0,35,0.08)',
                tension: 0.3,
                fill: true,
            }],
        },
        options: { plugins: { legend: { display: false } } },
    });

    new Chart(document.getElementById('chartPriority'), {
        type: 'doughnut',
        data: {
            labels: @json(collect($priorityChart)->pluck('label')),
            datasets: [{ data: @json(collect($priorityChart)->pluck('total')), backgroundColor: palette }],
        },
    });

    new Chart(document.getElementById('chartCategory'), {
        type: 'bar',
        data: {
            labels: @json(collect($categoryChart)->pluck('label')),
            datasets: [{ label: 'Ticket', data: @json(collect($categoryChart)->pluck('total')), backgroundColor: '#e60023' }],
        },
        options: { plugins: { legend: { display: false } }, indexAxis: 'y' },
    });

    new Chart(document.getElementById('chartTechnician'), {
        type: 'bar',
        data: {
            labels: @json(collect($technicianChart)->pluck('label')),
            datasets: [{ label: 'Ticket', data: @json(collect($technicianChart)->pluck('total')), backgroundColor: '#435ee5' }],
        },
        options: { plugins: { legend: { display: false } } },
    });
</script>
@endpush
@endsection
