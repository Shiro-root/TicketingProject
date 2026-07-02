@php
    $classes = match ($color) {
        'blue' => 'bg-blue-100 text-blue-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'green' => 'bg-green-100 text-green-700',
        'red' => 'bg-red-100 text-red-700',
        default => 'bg-stone text-mute',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-md py-xxs rounded-full text-caption-md font-medium $classes"]) }}>
    {{ $label }}
</span>
