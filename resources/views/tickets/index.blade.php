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
                <a href="{{ route('tickets.create') }}" class="btn-primary" data-shortcut="create-ticket">+ Buat Ticket</a>
            @endcan
        </div>
    </div>

    {{-- Bonus Feature: Saved Filter --}}
    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg flex flex-wrap items-center gap-sm">
        <span class="text-body-sm text-mute mr-xs">Filter Tersimpan:</span>

        @forelse ($savedFilters as $saved)
            <div class="flex items-center gap-xxs">
                <a href="{{ route('saved-filters.apply', $saved) }}"
                   class="text-caption-md px-sm py-xxs rounded-full bg-surface-card dark:bg-white/10 text-ink dark:text-on-dark hover:bg-secondary-bg dark:hover:bg-white/20">
                    {{ $saved->name }} @if($saved->is_default) ⭐ @endif
                </a>
                <form method="POST" action="{{ route('saved-filters.destroy', $saved) }}" onsubmit="return confirm('Hapus filter tersimpan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-caption-sm text-mute hover:text-error" title="Hapus">✕</button>
                </form>
            </div>
        @empty
            <span class="text-caption-sm text-mute">Belum ada filter tersimpan.</span>
        @endforelse

        <details class="ml-auto relative">
            <summary class="btn-tertiary list-none cursor-pointer">+ Simpan Filter Saat Ini</summary>
            <form method="POST" action="{{ route('saved-filters.store') }}"
                  class="absolute right-0 mt-sm w-72 bg-canvas dark:bg-surface-dark border border-hairline dark:border-white/10 rounded-md shadow-modal p-lg flex flex-col gap-md z-30">
                @csrf
                <input type="hidden" name="query_string" id="saved-filter-query-string">
                <div>
                    <label class="field-label">Nama Filter</label>
                    <input type="text" name="name" required placeholder="mis. Ticket Kritis Saya" class="field-input">
                </div>
                <label class="flex items-center gap-xs text-body-sm text-body select-none">
                    <input type="checkbox" name="is_default" value="1" class="rounded-sm border-ash text-primary">
                    Jadikan filter default (otomatis diterapkan saat buka halaman ini)
                </label>
                <button type="submit" class="btn-primary w-full">Simpan</button>
            </form>
        </details>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('tickets.index') }}" id="ticket-filter-form" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg flex flex-wrap items-end gap-md">
        <div class="flex-1 min-w-[200px]">
            <label class="field-label">Cari</label>
            <input type="text" name="search" data-shortcut="search-input" value="{{ request('search') }}" placeholder="No. Ticket, judul, nama... (tekan /)"
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

    {{-- Bonus Feature: Bulk Action toolbar — muncul otomatis saat ada baris dicentang --}}
    <form method="POST" action="{{ route('tickets.bulk') }}" id="bulk-action-form">
        @csrf
        <div id="bulk-toolbar" class="hidden bg-ink text-on-dark rounded-md p-lg flex flex-wrap items-center gap-md">
            <span class="text-body-sm"><span id="bulk-count">0</span> ticket dipilih</span>

            <select name="bulk_action" id="bulk-action-select" class="field-input !bg-canvas !text-ink w-auto">
                <option value="">— Pilih Aksi —</option>
                <option value="status">Ubah Status</option>
                <option value="assign">Tugaskan Teknisi</option>
                <option value="archive">Arsipkan</option>
                <option value="delete">Hapus</option>
            </select>

            <select name="status" id="bulk-status-select" class="field-input !bg-canvas !text-ink w-auto hidden">
                @foreach (\App\Enums\TicketStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>

            <select name="technician_id" id="bulk-technician-select" class="field-input !bg-canvas !text-ink w-auto hidden">
                <option value="">— Pilih Teknisi —</option>
                @foreach ($technicians as $tech)
                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary" onclick="return confirm('Jalankan aksi ini ke semua ticket yang dipilih?');">
                Jalankan
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto mt-lg">
            <table class="w-full text-body-sm">
                <thead>
                    <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                        <th class="px-lg py-md w-10">
                            <input type="checkbox" id="bulk-select-all" class="rounded-sm border-ash text-primary">
                        </th>
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
                        <tr class="border-b border-hairline dark:border-white/10 last:border-0 hover:bg-surface-card dark:hover:bg-white/5">
                            <td class="px-lg py-md" onclick="event.stopPropagation()">
                                <input type="checkbox" name="ids[]" value="{{ $ticket->id }}" class="bulk-row-checkbox rounded-sm border-ash text-primary">
                            </td>
                            <td class="px-lg py-md cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-body-strong text-ink dark:text-on-dark hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                                @if($ticket->is_sla_breached)
                                    <span class="ml-xs inline-block w-2 h-2 rounded-full bg-error" title="SLA Terlewati"></span>
                                @endif
                            </td>
                            <td class="px-lg py-md text-ink dark:text-on-dark truncate max-w-[240px] cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">{{ $ticket->subject }}</td>
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
                            <td colspan="8" class="px-lg py-xxl text-center text-mute">Tidak ada ticket ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div>{{ $tickets->links() }}</div>
</div>

@push('scripts')
<script>
    // Bonus Feature: Saved Filter — tangkap query-string aktif saat form "Simpan Filter" disubmit.
    document.querySelector('#saved-filter-query-string')?.closest('form')
        ?.addEventListener('submit', function () {
            document.getElementById('saved-filter-query-string').value = window.location.search;
        });

    // Bonus Feature: Bulk Action
    (function () {
        const selectAll = document.getElementById('bulk-select-all');
        const rowCheckboxes = () => Array.from(document.querySelectorAll('.bulk-row-checkbox'));
        const toolbar = document.getElementById('bulk-toolbar');
        const countLabel = document.getElementById('bulk-count');
        const actionSelect = document.getElementById('bulk-action-select');
        const statusSelect = document.getElementById('bulk-status-select');
        const technicianSelect = document.getElementById('bulk-technician-select');
        const bulkForm = document.getElementById('bulk-action-form');

        function updateToolbar() {
            const checked = rowCheckboxes().filter(cb => cb.checked);
            countLabel.textContent = checked.length;
            toolbar.classList.toggle('hidden', checked.length === 0);
        }

        selectAll?.addEventListener('change', () => {
            rowCheckboxes().forEach(cb => { cb.checked = selectAll.checked; });
            updateToolbar();
        });

        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('bulk-row-checkbox')) updateToolbar();
        });

        actionSelect?.addEventListener('change', () => {
            statusSelect.classList.toggle('hidden', actionSelect.value !== 'status');
            technicianSelect.classList.toggle('hidden', actionSelect.value !== 'assign');
        });

        bulkForm?.addEventListener('submit', (e) => {
            if (rowCheckboxes().filter(cb => cb.checked).length === 0 || ! actionSelect.value) {
                e.preventDefault();
                alert('Pilih minimal satu ticket dan satu aksi terlebih dahulu.');
            }
        });
    })();
</script>
@endpush
@endsection
