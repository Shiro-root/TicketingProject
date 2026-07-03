@props(['comment', 'ticket'])

<div class="flex gap-md {{ $comment->is_internal ? 'bg-amber-50 dark:bg-amber-900/10 rounded-md p-md' : '' }}">
    <span class="w-8 h-8 shrink-0 rounded-full bg-primary text-white flex items-center justify-center text-caption-md font-semibold">
        {{ $comment->user->initials() }}
    </span>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-sm flex-wrap">
            <span class="text-body-strong text-ink dark:text-on-dark">{{ $comment->user->name }}</span>
            @if($comment->is_internal)
                <span class="text-caption-sm px-sm py-xxs rounded-full bg-amber-200 text-amber-900">Catatan Internal</span>
            @endif
            <span class="text-caption-sm text-mute">{{ $comment->created_at->diffForHumans() }}</span>
            @if($comment->is_edited)
                <span class="text-caption-sm text-mute">(diedit)</span>
            @endif
        </div>

        <p class="text-body-sm text-ink dark:text-on-dark mt-xxs whitespace-pre-line">{{ $comment->body }}</p>

        @if($comment->attachments->isNotEmpty())
            <div class="flex flex-wrap gap-xs mt-sm">
                @foreach($comment->attachments as $attachment)
                    <a href="{{ route('tickets.attachments.download', [$ticket, $attachment]) }}"
                       class="text-caption-md px-sm py-xxs rounded-full bg-surface-card dark:bg-white/10 text-ink dark:text-on-dark hover:underline">
                        📎 {{ $attachment->original_name }} ({{ $attachment->humanSize() }})
                    </a>
                @endforeach
            </div>
        @endif

        @if($comment->user_id === auth()->id())
            <div class="flex gap-md mt-xs">
                <form method="POST" action="{{ route('tickets.comments.destroy', [$ticket, $comment]) }}"
                      onsubmit="return confirm('Hapus komentar ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-caption-sm text-error hover:underline">Hapus</button>
                </form>
            </div>
        @endif

        @if($comment->replies->isNotEmpty())
            <div class="flex flex-col gap-md mt-md pl-lg border-l-2 border-hairline dark:border-white/10">
                @foreach($comment->replies as $reply)
                    <x-comment-item :comment="$reply" :ticket="$ticket" />
                @endforeach
            </div>
        @endif
    </div>
</div>
