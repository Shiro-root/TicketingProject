@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Asset Terhapus</h1>
            <p class="text-body-md text-mute">Asset yang dihapus (soft delete) — bisa dipulihkan kapan saja.</p>
        </div>
        <a href="{{ route('assets.index') }}" class="btn-tertiary">&larr; Kembali ke Daftar Asset</a>
    </div>

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">Asset Tag</th>
                    <th class="px-lg py-md font-semibold">Nama</th>
                    <th class="px-lg py-md font-semibold">Department</th>
                    <th class="px-lg py-md font-semibold">Dihapus</th>
                    <th class="px-lg py-md font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assets as $asset)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0">
                        <td class="px-lg py-md text-body-strong text-ink dark:text-on-dark">{{ $asset->asset_tag }}</td>
                        <td class="px-lg py-md text-mute truncate max-w-[280px]">{{ $asset->name }}</td>
                        <td class="px-lg py-md text-mute">{{ $asset->department->name ?? '—' }}</td>
                        <td class="px-lg py-md text-mute">{{ $asset->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-lg py-md">
                            <form method="POST" action="{{ route('assets.restore', $asset->id) }}">
                                @csrf
                                <button type="submit" class="btn-secondary">Pulihkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-lg py-xxl text-center text-mute">Tidak ada asset terhapus.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $assets->links() }}</div>
</div>
@endsection