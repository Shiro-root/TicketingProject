@php($article = $article ?? null)

<div>
    <label for="title" class="field-label">Judul</label>
    <input id="title" type="text" name="title" value="{{ old('title', $article->title ?? '') }}" required
           class="field-input @error('title') has-error @enderror">
    @error('title') <p class="field-error">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
    <div>
        <label for="knowledge_base_category_id" class="field-label">Kategori KB</label>
        <select id="knowledge_base_category_id" name="knowledge_base_category_id" required
                class="field-input @error('knowledge_base_category_id') has-error @enderror">
            <option value="">— Pilih Kategori —</option>
            @foreach ($kbCategories as $cat)
                <option value="{{ $cat->id }}" @selected(old('knowledge_base_category_id', $article->knowledge_base_category_id ?? '') == $cat->id)>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('knowledge_base_category_id') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="category_id" class="field-label">Kategori Ticket Terkait (untuk AI Suggested Solution)</label>
        <select id="category_id" name="category_id" class="field-input">
            <option value="">— Tidak terhubung —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $article->category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label for="excerpt" class="field-label">Ringkasan (opsional)</label>
    <textarea id="excerpt" name="excerpt" rows="2" placeholder="Kosongkan untuk dibuat otomatis dari isi artikel"
              class="field-input">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
</div>

<div>
    <label for="content" class="field-label">Isi Artikel</label>
    <textarea id="content" name="content" rows="12" required
              class="field-input @error('content') has-error @enderror">{{ old('content', $article->content ?? '') }}</textarea>
    @error('content') <p class="field-error">{{ $message }}</p> @enderror
</div>

<div>
    <label for="tags" class="field-label">Tag</label>
    <select id="tags" name="tags[]" multiple class="field-input h-auto min-h-[88px]">
        @php($selectedTags = old('tags', $article?->tags->pluck('id')->all() ?? []))
        @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTags))>{{ $tag->name }}</option>
        @endforeach
    </select>
</div>

<label class="flex items-center gap-xs text-body-sm text-body select-none">
    <input type="checkbox" name="is_published" value="1"
           @checked(old('is_published', $article->is_published ?? true)) class="rounded-sm border-ash text-primary">
    Publikasikan sekarang
</label>