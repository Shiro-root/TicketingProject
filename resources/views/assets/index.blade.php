@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between flex-wrap gap-md">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Asset</h1>
            <p class="text-body-md text-mute">Kelola inventaris perangkat IT organisasi.</p>
        </div>
        <div class="flex items-center gap-sm">
            @if(auth()->user()->hasPermission('asset.manage'))
                <a href="{{ route('assets.trashed') }}" class="btn-tertiary">🗑 Asset Terhapus</a>
            @endif
            @can('create', \App\Models\Asset::class)
                <a href="{{ route('assets.create') }}" class="btn-primary">+ Tambah Asset</a>
            @endcan
        </div>
    </div>

    @include('partials.flash-messages')

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('assets.index') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg flex flex-wrap items-end gap-md">
        <div class="flex-1 min-w-[200px]">
            <label class="field-label">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Asset tag, nama, serial number..."
                   class="field-input">
        </div>

        <div>
            <label class="field-label">Jenis</label>
            <select name="type" class="field-input">
                <option value="">Semua</option>
                @foreach (\App\Enums\AssetType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="field-label">Status</label>
            <select name="status" class="field-input">
                <option value="">Semua</option>
                @foreach (\App\Enums\AssetStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="field-label">Department</label>
            <select name="department_id" class="field-input">
                <option value="">Semua</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="field-label">Garansi</label>
            <select name="warranty" class="field-input">
                <option value="">Semua</option>
                <option value="active" @selected(request('warranty') === 'active')>Masih Berlaku</option>
                <option value="expired" @selected(request('warranty') === 'expired')>Sudah Habis</option>
                <option value="none" @selected(request('warranty') === 'none')>Tidak Ada Data</option>
            </select>
        </div>

        <div class="flex gap-sm">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('assets.index') }}" class="btn-tertiary">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">Asset Tag</th>
                    <th class="px-lg py-md font-semibold">Nama</th>
                    <th class="px-lg py-md font-semibold">Jenis</th>
                    <th class="px-lg py-md font-semibold">Status</th>
                    <th class="px-lg py-md font-semibold">Department</th>
                    <th class="px-lg py-md font-semibold">Ditugaskan ke</th>
                    <th class="px-lg py-md font-semibold">Garansi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assets as $asset)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0 hover:bg-surface-card dark:hover:bg-white/5 cursor-pointer"
                        onclick="window.location='{{ route('assets.show', $asset) }}'">
                        <td class="px-lg py-md">
                            <a href="{{ route('assets.show', $asset) }}" class="text-body-strong text-ink dark:text-on-dark hover:underline">
                                {{ $asset->asset_tag }}
                            </a>
                        </td>
                        <td class="px-lg py-md text-ink dark:text-on-dark truncate max-w-[220px]">{{ $asset->name }}</td>
                        <td class="px-lg py-md text-mute">{{ $asset->type->label() }}</td>
                        <td class="px-lg py-md"><x-asset-status-badge :label="$asset->status->label()" :status="$asset->status->value" /></td>
                        <td class="px-lg py-md text-mute">{{ $asset->department->name ?? '—' }}</td>
                        <td class="px-lg py-md text-mute">{{ $asset->assignedUser->name ?? '— Tidak ditugaskan' }}</td>
                        <td class="px-lg py-md {{ $asset->isUnderWarranty() ? 'text-ink dark:text-on-dark' : 'text-mute' }}">
                            {{ $asset->warranty_expiry?->translatedFormat('d M Y') ?? '—' }}
                            @if($asset->warranty_expiry && ! $asset->isUnderWarranty())
                                <span class="ml-xs inline-block w-2 h-2 rounded-full bg-error" title="Garansi habis"></span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-lg py-xxl text-center text-mute">Tidak ada asset ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $assets->links() }}</div>
</div>
@endsection