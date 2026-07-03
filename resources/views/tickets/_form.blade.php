@php($ticket = $ticket ?? null)

<div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
    <div class="md:col-span-2 flex flex-col gap-lg">
        <div>
            <label for="subject" class="field-label">Judul Ticket</label>
            <input id="subject" type="text" name="subject" value="{{ old('subject', $ticket->subject ?? '') }}" required
                   class="field-input @error('subject') has-error @enderror">
            @error('subject') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="field-label">Deskripsi</label>
            <textarea id="description" name="description" rows="6" required
                      class="field-input @error('description') has-error @enderror">{{ old('description', $ticket->description ?? '') }}</textarea>
            @error('description') <p class="field-error">{{ $message }}</p> @enderror
            <div id="duplicate-warning" class="hidden mt-sm rounded-md bg-error/10 text-error text-body-sm px-md py-sm"></div>
            <div id="kb-suggestions" class="hidden mt-sm rounded-md bg-surface-card dark:bg-white/5 text-body-sm px-md py-sm"></div>
        </div>

        <div>
            <label for="attachments" class="field-label">Lampiran</label>
            <input id="attachments" type="file" name="attachments[]" multiple
                   accept=".jpg,.jpeg,.png,.pdf,.docx,.xlsx,.zip"
                   class="block w-full text-body-sm text-mute file:mr-md file:py-xs file:px-md file:rounded-full file:border-0 file:bg-secondary-bg file:text-body-sm file:text-ink hover:file:bg-secondary-pressed">
            <p class="text-caption-sm text-mute mt-xs">JPG, PNG, PDF, DOCX, XLSX, ZIP — maks 10MB per file, maks 5 file.</p>
            @error('attachments.*') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex flex-col gap-lg">
        <div>
            <label for="category_id" class="field-label">Kategori</label>
            <select id="category_id" name="category_id" required class="field-input @error('category_id') has-error @enderror">
                <option value="">— Pilih Kategori —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $ticket->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="department_id" class="field-label">Department</label>
            <select id="department_id" name="department_id" class="field-input">
                <option value="">— Otomatis dari kategori —</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id', $ticket->department_id ?? '') == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="priority" class="field-label">Prioritas</label>
            <select id="priority" name="priority" required class="field-input @error('priority') has-error @enderror">
                @foreach (\App\Enums\TicketPriority::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority', $ticket->priority->value ?? 'medium') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
            @error('priority') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="tags" class="field-label">Tag</label>
            <select id="tags" name="tags[]" multiple class="field-input h-auto min-h-[88px]">
                @php($selectedTags = old('tags', $ticket?->tags->pluck('id')->all() ?? []))
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTags))>{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="asset_ids" class="field-label">Asset Terkait</label>
            <select id="asset_ids" name="asset_ids[]" multiple class="field-input h-auto min-h-[88px]">
                @php($selectedAssets = old('asset_ids', $ticket?->assets->pluck('id')->all() ?? []))
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}" @selected(in_array($asset->id, $selectedAssets))>{{ $asset->asset_tag }} — {{ $asset->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const subject = document.getElementById('subject');
        const description = document.getElementById('description');
        const categorySelect = document.getElementById('category_id');
        const dupBox = document.getElementById('duplicate-warning');
        const kbBox = document.getElementById('kb-suggestions');
        let timer;

        function check() {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                const s = subject.value.trim();
                if (s.length < 8) { dupBox.classList.add('hidden'); kbBox.classList.add('hidden'); return; }

                try {
                    const res = await fetch('{{ route('tickets.check-duplicates') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ subject: s }),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.has_duplicates) {
                            dupBox.innerHTML = '⚠️ Ditemukan ticket serupa yang baru saja Anda buat: ' +
                                data.duplicates.map(d => `<a href="${d.url}" class="underline font-semibold">${d.ticket_number}</a>`).join(', ') +
                                '. Pastikan ini bukan duplikat sebelum melanjutkan.';
                            dupBox.classList.remove('hidden');
                        } else {
                            dupBox.classList.add('hidden');
                        }
                    }
                } catch (e) { /* diamkan — jangan blokir submit form karena AJAX gagal */ }

                // AI Suggested Solution — cari artikel KB relevan dari gabungan judul + deskripsi.
                const combinedText = (s + ' ' + description.value.trim()).trim();
                if (combinedText.length < 6) { kbBox.classList.add('hidden'); return; }

                try {
                    const res = await fetch('{{ route('knowledge-base.suggest') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ text: combinedText, category_id: categorySelect.value || null }),
                    });
                    if (!res.ok) return;
                    const data = await res.json();

                    if (data.suggestions.length) {
                        kbBox.innerHTML = '💡 <strong>Mungkin membantu:</strong><ul class="list-disc list-inside mt-xs">' +
                            data.suggestions.map(a => `<li><a href="${a.url}" target="_blank" class="underline font-semibold">${a.title}</a></li>`).join('') +
                            '</ul>';
                        kbBox.classList.remove('hidden');
                    } else {
                        kbBox.classList.add('hidden');
                    }
                } catch (e) { /* diamkan */ }
            }, 500);
        }

        subject?.addEventListener('input', check);
        description?.addEventListener('input', check);
    })();
</script>
@endpush
