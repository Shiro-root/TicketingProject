@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between flex-wrap gap-md">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Knowledge Base</h1>
            <p class="text-body-md text-mute">Kumpulan artikel FAQ, Tutorial, SOP, dan Dokumentasi.</p>
        </div>
        <div class="flex items-center gap-sm">
            @can('viewAny', \App\Models\KnowledgeBaseArticle::class)
                @if(auth()->user()->hasPermission('kb.manage'))
                    <a href="{{ route('knowledge-base.trashed') }}" class="btn-tertiary">🗑 Artikel Terhapus</a>
                @endif
            @endcan
            @can('create', \App\Models\KnowledgeBaseArticle::class)
                <a href="{{ route('knowledge-base.create') }}" class="btn-primary">+ Tulis Artikel</a>
            @endcan
        </div>
    </div>

    @include('partials.flash-messages')

    <form method="GET" action="{{ route('knowledge-base.index') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg flex flex-wrap items-end gap-md">
        <div class="flex-1 min-w-[240px]">
            <label class="field-label">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau isi artikel..."
                   class="field-input">
        </div>
        <div>
            <label class="field-label">Kategori</label>
            <select name="knowledge_base_category_id" class="field-input">
                <option value="">Semua</option>
                @foreach ($kbCategories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('knowledge_base_category_id') === (string) $cat->id)>
                        {{ $cat->name }} ({{ $cat->articles_count }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-sm">
            <button type="submit" class="btn-primary">Cari</button>
            <a href="{{ route('knowledge-base.index') }}" class="btn-tertiary">Reset</a>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
        @forelse ($articles as $article)
            <a href="{{ route('knowledge-base.show', $article) }}"
               class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg flex flex-col gap-sm hover:border-primary transition-colors">
                <span class="text-caption-md px-sm py-xxs rounded-full bg-surface-card dark:bg-white/10 text-mute self-start">
                    {{ $article->kbCategory->name ?? '—' }}
                </span>
                <h2 class="text-heading-md text-ink dark:text-on-dark line-clamp-2">{{ $article->title }}</h2>
                <p class="text-body-sm text-mute line-clamp-3">{{ $article->excerpt }}</p>
                <div class="flex items-center justify-between text-caption-sm text-mute mt-auto pt-sm">
                    <span>👁 {{ $article->view_count }} dilihat</span>
                    <span>{{ $article->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center text-body-sm text-mute py-xxl">
                Tidak ada artikel ditemukan.
            </div>
        @endforelse
    </div>

    <div>{{ $articles->links() }}</div>
</div>
@endsection