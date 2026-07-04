@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-md">
        <div>
            <div class="flex items-center gap-sm flex-wrap mb-xxs">
                <h1 class="text-heading-xl text-ink dark:text-on-dark">{{ $ticket->ticket_number }}</h1>
                <x-status-badge :label="$ticket->status->label()" :color="$ticket->status->color()" />
                <x-priority-badge :label="$ticket->priority->label()" :color="$ticket->priority->color()" />
                @if($ticket->is_sla_breached)
                    <span class="text-caption-md px-sm py-xxs rounded-full bg-error text-white">SLA Terlewati</span>
                @endif
            </div>
            <p class="text-heading-md text-ink dark:text-on-dark">{{ $ticket->subject }}</p>
        </div>

        <div class="flex items-center gap-sm">
            <form method="POST" action="{{ route('tickets.bookmark', $ticket) }}">
                @csrf
                <button type="submit" class="btn-tertiary" title="Favorite">
                    {{ $isBookmarked ? '★ Favorit' : '☆ Favorit' }}
                </button>
            </form>
            <form method="POST" action="{{ route('tickets.watch', $ticket) }}">
                @csrf
                <button type="submit" class="btn-tertiary">
                    {{ $isWatching ? '🔔 Berhenti Pantau' : '🔕 Pantau' }}
                </button>
            </form>
            @can('update', $ticket)
                <a href="{{ route('tickets.edit', $ticket) }}" class="btn-secondary">Edit</a>
            @endcan
        </div>
    </div>

    @include('partials.flash-messages')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-xl">
        {{-- Main column --}}
        <div class="lg:col-span-2 flex flex-col gap-xl">
            <div class="card p-xl">
                <h2 class="text-heading-md text-ink dark:text-on-dark mb-md">Deskripsi</h2>
                <p class="text-body-md text-body dark:text-on-dark-mute whitespace-pre-line">{{ $ticket->description }}</p>

                @if($ticket->attachments->where('ticket_comment_id', null)->isNotEmpty())
                    <div class="flex flex-wrap gap-xs mt-lg">
                        @foreach($ticket->attachments->where('ticket_comment_id', null) as $attachment)
                            <a href="{{ route('tickets.attachments.download', [$ticket, $attachment]) }}"
                               class="text-caption-md px-sm py-xxs rounded-full bg-surface-card dark:bg-white/10 text-ink dark:text-on-dark hover:underline">
                                📎 {{ $attachment->original_name }} ({{ $attachment->humanSize() }})
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($ticket->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-xs mt-lg">
                        @foreach($ticket->tags as $tag)
                            <span class="text-caption-md px-sm py-xxs rounded-full bg-stone/30 text-charcoal">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Workflow actions --}}
            <div class="card p-xl">
                <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Aksi Ticket</h2>
                <div class="flex flex-wrap gap-sm">
                    @if($ticket->assigned_to === auth()->id() && $ticket->status->value === 'assigned')
                        <form method="POST" action="{{ route('tickets.accept', $ticket) }}">
                            @csrf
                            <button type="submit" class="btn-primary">Terima Ticket</button>
                        </form>
                    @endif

                    @foreach($ticket->status->allowedTransitions() as $target)
                        @if(! in_array($target->value, ['closed', 'archived']))
                            <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $target->value }}">
                                <button type="submit" class="btn-secondary">Ubah ke {{ $target->label() }}</button>
                            </form>
                        @endif
                    @endforeach

                    @can('close', $ticket)
                        @if($ticket->status->value === 'resolved')
                            <form method="POST" action="{{ route('tickets.close', $ticket) }}">
                                @csrf
                                <button type="submit" class="btn-primary">Tutup Ticket</button>
                            </form>
                        @endif
                    @endcan

                    @can('reopen', $ticket)
                        @if(in_array($ticket->status->value, ['resolved', 'closed']))
                            <form method="POST" action="{{ route('tickets.reopen', $ticket) }}">
                                @csrf
                                <button type="submit" class="btn-tertiary">Buka Kembali</button>
                            </form>
                        @endif
                    @endcan

                    <form method="POST" action="{{ route('tickets.duplicate', $ticket) }}">
                        @csrf
                        <button type="submit" class="btn-tertiary">Duplikat</button>
                    </form>

                    @can('archive', $ticket)
                        <x-confirm-modal
                            id="archive-ticket-{{ $ticket->id }}"
                            title="Arsipkan Ticket?"
                            description="Ticket {{ $ticket->ticket_number }} akan dipindahkan ke arsip."
                            :action="route('tickets.archive', $ticket)"
                            method="POST"
                            confirmLabel="Ya, Arsipkan"
                            confirmClass="btn-primary"
                            triggerLabel="Arsipkan"
                            triggerClass="btn-tertiary"
                        />
                    @endcan

                    @can('delete', $ticket)
                        <x-confirm-modal
                            id="delete-ticket-{{ $ticket->id }}"
                            title="Hapus Ticket?"
                            description="Ticket {{ $ticket->ticket_number }} akan dihapus. Anda masih bisa memulihkannya lewat menu Restore."
                            :action="route('tickets.destroy', $ticket)"
                            method="DELETE"
                            confirmLabel="Ya, Hapus"
                            confirmClass="btn-primary"
                            triggerLabel="Hapus"
                            triggerClass="btn-tertiary text-error"
                        />
                    @endcan
                </div>

                @can('merge', $ticket)
                    {{--
                        Merge needs a free-text "target ticket id" field submitted together
                        with the confirmation, so it keeps its own lightweight Alpine confirm
                        step instead of <x-confirm-modal>, which only wraps @csrf-only forms.
                    --}}
                    <div x-data="{ confirmingMerge: false }" class="mt-lg pt-lg border-t border-hairline dark:border-white/10">
                        <form
                            method="POST"
                            action="{{ route('tickets.merge', $ticket) }}"
                            @submit="if (! confirmingMerge) { $event.preventDefault(); confirmingMerge = true; }"
                            class="flex items-end gap-sm flex-wrap"
                        >
                            @csrf
                            <div class="flex-1 min-w-[200px] max-w-xs">
                                <label class="field-label">Gabungkan ke Ticket ID</label>
                                <input type="number" name="into_id" required placeholder="Masukkan ID ticket tujuan" class="field-input">
                            </div>
                            <button type="submit" class="btn-tertiary">Merge</button>
                        </form>

                        <div x-show="confirmingMerge" x-cloak x-transition.opacity class="modal-scrim" @keydown.escape.window="confirmingMerge = false">
                            <div class="modal-card" style="max-width: 420px" @click.outside="confirmingMerge = false">
                                <button type="button" class="modal-close" @click="confirmingMerge = false" aria-label="Tutup">✕</button>
                                <h2 class="text-heading-lg text-ink dark:text-on-dark mb-xs pr-xl">Gabungkan Ticket?</h2>
                                <p class="text-body-sm text-mute mb-xl">Ticket {{ $ticket->ticket_number }} akan digabungkan ke ticket tujuan dan diarsipkan.</p>
                                <div class="flex justify-end gap-sm">
                                    <button type="button" class="btn-tertiary" @click="confirmingMerge = false">Batal</button>
                                    <button type="button" class="btn-primary" @click="confirmingMerge = false; $el.closest('[x-data]').querySelector('form').submit()">Ya, Gabungkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>

            {{-- Rating (only creator, only when closed) --}}
            @can('rate', $ticket)
                <div class="card p-xl">
                    <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Beri Rating</h2>
                    <form method="POST" action="{{ route('tickets.rate', $ticket) }}" class="flex flex-col gap-md max-w-md">
                        @csrf
                        <div class="flex gap-xs">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer text-heading-lg">
                                    <input type="radio" name="rating" value="{{ $i }}" class="hidden peer" @required(true)>
                                    <span class="peer-checked:text-primary text-stone">★</span>
                                </label>
                            @endfor
                        </div>
                        <textarea name="feedback" rows="2" placeholder="Feedback (opsional)" class="field-input"></textarea>
                        <button type="submit" class="btn-primary self-start">Kirim Rating</button>
                    </form>
                </div>
            @endcan

            {{-- Comments --}}
            <div id="comments" class="card p-xl">
                <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Diskusi ({{ $comments->count() }})</h2>

                <div class="flex flex-col gap-lg mb-xl">
                    @forelse($comments as $comment)
                        <x-comment-item :comment="$comment" :ticket="$ticket" />
                    @empty
                        <p class="text-body-sm text-mute">Belum ada komentar.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" enctype="multipart/form-data" class="flex flex-col gap-md border-t border-hairline dark:border-white/10 pt-lg">
                    @csrf
                    <textarea name="body" rows="3" required placeholder="Tulis komentar... gunakan @nama untuk mention"
                              class="field-input"></textarea>
                    <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.docx,.xlsx,.zip"
                           class="text-body-sm text-mute file:mr-md file:py-xs file:px-md file:rounded-full file:border-0 file:bg-secondary-bg file:text-body-sm file:text-ink file:cursor-pointer hover:file:bg-secondary-pressed">
                    <div class="flex items-center justify-between flex-wrap gap-sm">
                        @unless(auth()->user()->hasRole('employee', 'guest'))
                            <label class="flex items-center gap-xs text-body-sm text-mute select-none">
                                <input type="checkbox" name="is_internal" value="1">
                                Catatan internal (tidak terlihat oleh user)
                            </label>
                        @else
                            <span></span>
                        @endunless
                        <button type="submit" class="btn-primary">Kirim</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="flex flex-col gap-lg">
            <div class="card p-lg">
                <h2 class="text-body-strong text-ink dark:text-on-dark mb-md">Informasi</h2>
                <dl class="flex flex-col gap-sm text-body-sm">
                    <div class="flex justify-between"><dt class="text-mute">Kategori</dt><dd class="text-ink dark:text-on-dark">{{ $ticket->category->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-mute">Department</dt><dd class="text-ink dark:text-on-dark">{{ $ticket->department->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-mute">Dibuat oleh</dt><dd class="text-ink dark:text-on-dark">{{ $ticket->creator->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-mute">Dibuat</dt><dd class="text-ink dark:text-on-dark">{{ $ticket->created_at->translatedFormat('d M Y H:i') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-mute">Due</dt><dd class="{{ $ticket->is_sla_breached ? 'text-error font-semibold' : 'text-ink dark:text-on-dark' }}">{{ $ticket->due_at?->translatedFormat('d M Y H:i') ?? '—' }}</dd></div>
                    @if($ticket->resolved_at)
                        <div class="flex justify-between"><dt class="text-mute">Resolved</dt><dd class="text-ink dark:text-on-dark">{{ $ticket->resolved_at->translatedFormat('d M Y H:i') }}</dd></div>
                    @endif
                    @if($ticket->rating)
                        <div class="flex justify-between"><dt class="text-mute">Rating</dt><dd class="text-ink dark:text-on-dark">{{ str_repeat('★', $ticket->rating) }}{{ str_repeat('☆', 5 - $ticket->rating) }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Assignment --}}
            @can('assign', $ticket)
                <div class="card p-lg">
                    <h2 class="text-body-strong text-ink dark:text-on-dark mb-md">Teknisi</h2>
                    @if($ticket->assignee)
                        <div class="flex items-center gap-sm mb-md">
                            <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-caption-md font-semibold">{{ $ticket->assignee->initials() }}</span>
                            <span class="text-body-sm text-ink dark:text-on-dark">{{ $ticket->assignee->name }}</span>
                        </div>
                    @else
                        <p class="text-body-sm text-mute mb-md">Belum ditugaskan.</p>
                    @endif

                    <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex flex-col gap-sm">
                        @csrf
                        <select name="technician_id" required class="field-input">
                            <option value="">— Pilih Teknisi —</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" @selected($ticket->assigned_to === $tech->id)>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-secondary">{{ $ticket->assignee ? 'Tugaskan Ulang' : 'Tugaskan' }}</button>
                    </form>

                    @if($ticket->technicians->isNotEmpty())
                        <div class="mt-md pt-md border-t border-hairline dark:border-white/10">
                            <p class="text-caption-md text-mute mb-xs">Tim Teknisi</p>
                            <div class="flex flex-wrap gap-xs">
                                @foreach($ticket->technicians as $tech)
                                    <span class="text-caption-md px-sm py-xxs rounded-full bg-surface-card dark:bg-white/10 text-ink dark:text-on-dark">
                                        {{ $tech->name }} @if($tech->pivot->is_lead) <strong>(Lead)</strong> @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endcan

            {{-- Activity timeline --}}
            <div class="card p-lg">
                <h2 class="text-body-strong text-ink dark:text-on-dark mb-md">Aktivitas</h2>
                <div class="flex flex-col">
                    @forelse($ticket->activities as $activity)
                        <x-timeline-item :activity="$activity" />
                    @empty
                        <p class="text-body-sm text-mute">Belum ada aktivitas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
