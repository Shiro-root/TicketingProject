@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Artikel Terhapus</h1>
            <p class="text-body-md text-mute">Artikel yang dihapus (soft delete) — bisa dipulihkan kapan saja.</p>
        </div>
        <a href="{{ route('knowledge-base.index') }}" class="btn-tertiary">&larr; Kembali ke Knowledge Base</a>
    </div>

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">Judul</th>
                    <th class="px-lg py-md font-semibold">Kategori</th>
                    <th class="px-lg py-md font-semibold">Penulis</th>
                    <th class="px-lg py-md font-semibold">Dihapus</th>
                    <th class="px-lg py-md font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $article)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0">
                        <td class="px-lg py-md text-body-strong text-ink dark:text-on-dark truncate max-w-[320px]">{{ $article->title }}</td>
                        <td class="px-lg py-md text-mute">{{ $article->kbCategory->name ?? '—' }}</td>
                        <td class="px-lg py-md text-mute">{{ $article->author->name ?? '—' }}</td>
                        <td class="px-lg py-md text-mute">{{ $article->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-lg py-md">
                            <form method="POST" action="{{ route('knowledge-base.restore', $article->id) }}">
                                @csrf
                                <button type="submit" class="btn-secondary">Pulihkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-lg py-xxl text-center text-mute">Tidak ada artikel terhapus.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $articles->links() }}</div>
</div>
@endsection