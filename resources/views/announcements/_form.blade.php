@php($announcement = $announcement ?? null)

<div>
    <label for="title" class="field-label">Judul</label>
    <input id="title" type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}" required
           class="field-input @error('title') has-error @enderror">
    @error('title') <p class="field-error">{{ $message }}</p> @enderror
</div>

<div>
    <label for="content" class="field-label">Isi Pengumuman</label>
    <textarea id="content" name="content" rows="4" required
              class="field-input @error('content') has-error @enderror">{{ old('content', $announcement->content ?? '') }}</textarea>
    @error('content') <p class="field-error">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
    <div>
        <label for="type" class="field-label">Jenis</label>
        <select id="type" name="type" required class="field-input @error('type') has-error @enderror">
            @foreach (['info' => 'Info', 'warning' => 'Peringatan', 'success' => 'Sukses', 'danger' => 'Penting/Darurat'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $announcement->type ?? 'info') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="starts_at" class="field-label">Mulai Tampil (opsional)</label>
        <input id="starts_at" type="datetime-local" name="starts_at"
               value="{{ old('starts_at', optional($announcement->starts_at ?? null)->format('Y-m-d\TH:i')) }}"
               class="field-input @error('starts_at') has-error @enderror">
        @error('starts_at') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="ends_at" class="field-label">Berhenti Tampil (opsional)</label>
        <input id="ends_at" type="datetime-local" name="ends_at"
               value="{{ old('ends_at', optional($announcement->ends_at ?? null)->format('Y-m-d\TH:i')) }}"
               class="field-input @error('ends_at') has-error @enderror">
        @error('ends_at') <p class="field-error">{{ $message }}</p> @enderror
    </div>
</div>

<label class="flex items-center gap-xs text-body-sm text-body select-none">
    <input type="checkbox" name="is_active" value="1"
           @checked(old('is_active', $announcement->is_active ?? true)) class="rounded-sm border-ash text-primary">
    Aktifkan sekarang
</label>
<p class="text-caption-sm text-mute">
    Kosongkan "Mulai/Berhenti Tampil" untuk pengumuman yang tampil terus selama masih aktif.
    Pengumuman hanya tampil jika <strong>Aktifkan</strong> dicentang DAN berada dalam rentang waktu di atas.
</p>
