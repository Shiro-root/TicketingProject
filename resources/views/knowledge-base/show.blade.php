@extends('layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row gap-xl max-w-6xl">
    <div class="flex-1 flex flex-col gap-lg">
        <div>
            <a href="{{ route('knowledge-base.index') }}" class="link-inline text-body-sm">&larr; Kembali ke Knowledge Base</a>
        </div>

        @include('partials.flash-messages')

        <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <div class="flex items-center gap-sm mb-md flex-wrap">
                <span class="text-caption-md px-sm py-xxs rounded-full bg-surface-card dark:bg-white/10 text-mute">
                    {{ $article->kbCategory->name }}
                </span>
                @unless($article->is_published)
                    <span class="text-caption-md px-sm py-xxs rounded-full bg-amber-100 text-amber-700">Draft</span>
                @endunless
                @foreach ($article->tags as $tag)
                    <span class="text-caption-md px-sm py-xxs rounded-full bg-stone/30 text-charcoal">#{{ $tag->name }}</span>
                @endforeach
            </div>

            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-sm">{{ $article->title }}</h1>

            <div class="flex items-center gap-md text-caption-sm text-mute mb-xl pb-lg border-b border-hairline dark:border-white/10">
                <span>Oleh {{ $article->author->name ?? '—' }}</span>
                <span>·</span>
                <span>{{ $article->created_at->translatedFormat('d M Y') }}</span>
                <span>·</span>
                <span>👁 {{ $article->view_count }} dilihat</span>
            </div>

            <div class="text-body-md text-body dark:text-on-dark-mute whitespace-pre-line">{{ $article->content }}</div>

            <div class="flex gap-sm mt-xl pt-lg border-t border-hairline dark:border-white/10">
                @can('update', $article)
                    <a href="{{ route('knowledge-base.edit', $article) }}" class="btn-secondary">Edit Artikel</a>
                @endcan
                @can('delete', $article)
                    <form method="POST" action="{{ route('knowledge-base.destroy', $article) }}"
                          onsubmit="return confirm('Hapus artikel ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-tertiary text-error">Hapus</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="lg:w-72 shrink-0 flex flex-col gap-lg">
        <div class="bg-surface-card dark:bg-white/5 rounded-md p-lg">
            <h2 class="text-body-strong text-ink dark:text-on-dark mb-md">Artikel Terkait</h2>
            <div class="flex flex-col gap-sm">
                @forelse ($related as $r)
                    <a href="{{ route('knowledge-base.show', $r) }}" class="text-body-sm text-ink dark:text-on-dark hover:underline">
                        {{ $r->title }}
                    </a>
                @empty
                    <p class="text-body-sm text-mute">Belum ada artikel terkait.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection