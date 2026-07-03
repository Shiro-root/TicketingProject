@props([
    'id',
    'maxWidth' => '420px',
])

{{--
    Generic modal shell, per DESIGN.md {component.modal-card}: 50%-opacity scrim
    over the page + a 16px ambient shadow lifting the card above the content.

    Open it from anywhere with:  @click="$dispatch('open-modal-{{ '{id}' }}')"
    Close it from inside with:   @click="open = false"
--}}
<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $id }}.window="open = true"
    x-on:close-modal-{{ $id }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    x-transition.opacity
    class="modal-scrim"
    role="dialog"
    aria-modal="true"
>
    <div
        class="modal-card"
        style="max-width: {{ $maxWidth }}"
        x-show="open"
        x-transition.scale.origin.top
        @click.outside="open = false"
    >
        <button type="button" class="modal-close" @click="open = false" aria-label="Tutup">
            ✕
        </button>

        {{ $slot }}
    </div>
</div>