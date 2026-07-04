@props([
    'id',
    'title' => 'Konfirmasi',
    'description' => 'Apakah Anda yakin ingin melanjutkan?',
    'action',
    'method' => 'POST',
    'confirmLabel' => 'Ya, Lanjutkan',
    'confirmClass' => 'btn-primary',
    'triggerLabel' => 'Hapus',
    'triggerClass' => 'btn-tertiary text-error',
])

{{--
    Drop-in replacement for `onsubmit="return confirm('...')"` — styled per DESIGN.md
    instead of relying on the unstyled native browser confirm() dialog.

    Usage:
    <x-confirm-modal
        id="delete-ticket-{{ '{$ticket->id}' }}"
        title="Hapus Ticket?"
        description="Ticket ini akan dihapus. Anda masih bisa memulihkannya lewat menu Restore."
        :action="route('tickets.destroy', $ticket)"
        method="DELETE"
        confirmLabel="Ya, Hapus"
        triggerLabel="Hapus"
    />
--}}
<button type="button" class="{{ $triggerClass }}" @click="$dispatch('open-modal-{{ $id }}')">
    {{ $triggerLabel }}
</button>

<x-modal :id="$id">
    <h2 class="text-heading-lg text-ink dark:text-on-dark mb-xs pr-xl">{{ $title }}</h2>
    <p class="text-body-sm text-mute mb-xl">{{ $description }}</p>

    <form method="POST" action="{{ $action }}" class="flex justify-end gap-sm">
        @csrf
        @unless(strtoupper($method) === 'POST')
            @method($method)
        @endunless

        <button type="button" class="btn-tertiary" @click="open = false">Batal</button>
        <button type="submit" class="{{ $confirmClass }}">{{ $confirmLabel }}</button>
    </form>
</x-modal>
