@php
    $classes = match ($color) {
        'gray' => 'bg-stone/40 text-charcoal',
        'blue' => 'bg-blue-100 text-blue-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'red' => 'bg-red-100 text-red-700',
        default => 'bg-stone text-mute',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-md py-xxs rounded-full text-caption-md font-medium $classes"]) }}>
    {{ $label }}
</span>
