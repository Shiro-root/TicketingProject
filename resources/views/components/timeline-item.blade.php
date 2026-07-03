@props(['activity'])

<div class="flex gap-md">
    <div class="flex flex-col items-center">
        <span class="w-2.5 h-2.5 rounded-full bg-primary mt-xs"></span>
        <span class="w-px flex-1 bg-hairline dark:bg-white/10"></span>
    </div>
    <div class="pb-lg">
        <p class="text-body-sm text-ink dark:text-on-dark">
            <span class="text-body-strong">{{ $activity->user?->name ?? 'Sistem' }}</span>
            {{ $activity->description }}
        </p>
        <p class="text-caption-sm text-mute">{{ $activity->created_at->translatedFormat('d M Y, H:i') }} · {{ $activity->created_at->diffForHumans() }}</p>
    </div>
</div>
