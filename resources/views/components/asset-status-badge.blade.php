@php
    $classes = match ($status) {
        'available' => 'bg-green-100 text-green-700',
        'in_use' => 'bg-blue-100 text-blue-700',
        'under_maintenance' => 'bg-amber-100 text-amber-700',
        'retired' => 'bg-stone/40 text-charcoal',
        'lost' => 'bg-red-100 text-red-700',
        default => 'bg-stone text-mute',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-md py-xxs rounded-full text-caption-md font-medium $classes"]) }}>
    {{ $label }}
</span>