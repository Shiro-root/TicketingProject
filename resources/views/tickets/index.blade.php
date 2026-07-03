@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between flex-wrap gap-md">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Ticket</h1>
            <p class="text-body-md text-mute">Kelola seluruh ticket helpdesk.</p>
        </div>
        <div class="flex items-center gap-sm">
            @if(auth()->user()->hasPermission('ticket.delete'))
                <a href="{{ route('tickets.trashed') }}" class="btn-tertiary">🗑 Ticket Terhapus</a>
            @endif
            @can('create', \App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}" class="btn-primary">+ Buat Ticket</a>
            @endcan
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('tickets.index') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg flex flex-wrap items-end gap-md">
        <div class="flex-1 min-w-[200px]">
            <label class="field-label">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Ticket, judul, nama..."
                   class="field-input">
        </div>

        <div>
            <label class="field-label">Status</label>
            <select name="status" class="field-input">
                <option value="">Semua</option>
                @foreach (\App\Enums\TicketStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="field-label">Prioritas</label>
            <select name="priority" class="field-input">
                <option value="">Semua</option>
                @foreach (\App\Enums\TicketPriority::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="field-label">Kategori</label>
            <select name="category_id" class="field-input">
                <option value="">Semua</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="field-label">Department</label>
            <select name="department_id" class="field-input">
                <option value="">Semua</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="field-label">Teknisi</label>
            <select name="assigned_to" class="field-input">
                <option value="">Semua</option>
                @foreach ($technicians as $tech)
                    <option value="{{ $tech->id }}" @selected((string) request('assigned_to') === (string) $tech->id)>{{ $tech->name }}</option>
                @endforeach
            </select>
        </div>

        <label class="flex items-center gap-xs text-body-sm text-body select-none pb-sm">
            <input type="checkbox" name="sla_breached" value="1" @checked(request()->boolean('sla_breached')) class="rounded-sm border-ash text-primary">
            SLA Terlewati
        </label>

        <div class="flex gap-sm">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('tickets.index') }}" class="btn-tertiary">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">No. Ticket</th>
                    <th class="px-lg py-md font-semibold">Judul</th>
                    <th class="px-lg py-md font-semibold">Kategori</th>
                    <th class="px-lg py-md font-semibold">Prioritas</th>
                    <th class="px-lg py-md font-semibold">Status</th>
                    <th class="px-lg py-md font-semibold">Teknisi</th>
                    <th class="px-lg py-md font-semibold">Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0 hover:bg-surface-card dark:hover:bg-white/5 cursor-pointer"
                        onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                        <td class="px-lg py-md">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-body-strong text-ink dark:text-on-dark hover:underline">
                                {{ $ticket->ticket_number }}
                            </a>
                            @if($ticket->is_sla_breached)
                                <span class="ml-xs inline-block w-2 h-2 rounded-full bg-error" title="SLA Terlewati"></span>
                            @endif
                        </td>
                        <td class="px-lg py-md text-ink dark:text-on-dark truncate max-w-[280px]">{{ $ticket->subject }}</td>
                        <td class="px-lg py-md text-mute">{{ $ticket->category->name ?? '—' }}</td>
                        <td class="px-lg py-md"><x-priority-badge :label="$ticket->priority->label()" :color="$ticket->priority->color()" /></td>
                        <td class="px-lg py-md"><x-status-badge :label="$ticket->status->label()" :color="$ticket->status->color()" /></td>
                        <td class="px-lg py-md text-mute">{{ $ticket->assignee->name ?? '— Belum ditugaskan' }}</td>
                        <td class="px-lg py-md {{ $ticket->is_sla_breached ? 'text-error font-semibold' : 'text-mute' }}">
                            {{ $ticket->due_at?->translatedFormat('d M Y H:i') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-lg py-xxl text-center text-mute">Tidak ada ticket ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $tickets->links() }}</div>
</div>
@endsection
