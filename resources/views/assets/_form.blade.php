@php($asset = $asset ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
    <div>
        <label for="asset_tag" class="field-label">Asset Tag</label>
        <input id="asset_tag" type="text" name="asset_tag" value="{{ old('asset_tag', $asset->asset_tag ?? '') }}" required
               placeholder="mis. AST-00123"
               class="field-input @error('asset_tag') has-error @enderror">
        @error('asset_tag') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="name" class="field-label">Nama Asset</label>
        <input id="name" type="text" name="name" value="{{ old('name', $asset->name ?? '') }}" required
               class="field-input @error('name') has-error @enderror">
        @error('name') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="type" class="field-label">Jenis</label>
        <select id="type" name="type" required class="field-input @error('type') has-error @enderror">
            @foreach (\App\Enums\AssetType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $asset->type->value ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="field-label">Status</label>
        <select id="status" name="status" required class="field-input @error('status') has-error @enderror">
            @foreach (\App\Enums\AssetStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $asset->status->value ?? 'available') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="brand" class="field-label">Brand</label>
        <input id="brand" type="text" name="brand" value="{{ old('brand', $asset->brand ?? '') }}"
               class="field-input @error('brand') has-error @enderror">
        @error('brand') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="model" class="field-label">Model</label>
        <input id="model" type="text" name="model" value="{{ old('model', $asset->model ?? '') }}"
               class="field-input @error('model') has-error @enderror">
        @error('model') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="serial_number" class="field-label">Serial Number</label>
        <input id="serial_number" type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number ?? '') }}"
               class="field-input @error('serial_number') has-error @enderror">
        @error('serial_number') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="location" class="field-label">Lokasi</label>
        <input id="location" type="text" name="location" value="{{ old('location', $asset->location ?? '') }}"
               placeholder="mis. Lantai 2, Ruang Server"
               class="field-input @error('location') has-error @enderror">
        @error('location') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="department_id" class="field-label">Department</label>
        <select id="department_id" name="department_id" class="field-input">
            <option value="">— Tidak terhubung —</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $asset->department_id ?? '') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="assigned_to" class="field-label">Ditugaskan ke</label>
        <select id="assigned_to" name="assigned_to" class="field-input">
            <option value="">— Tidak ditugaskan —</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('assigned_to', $asset->assigned_to ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="purchase_date" class="field-label">Tanggal Pembelian</label>
        <input id="purchase_date" type="date" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date ?? null)->format('Y-m-d')) }}"
               class="field-input @error('purchase_date') has-error @enderror">
        @error('purchase_date') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="purchase_price" class="field-label">Harga Pembelian (Rp)</label>
        <input id="purchase_price" type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price ?? '') }}"
               class="field-input @error('purchase_price') has-error @enderror">
        @error('purchase_price') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="warranty_expiry" class="field-label">Berakhir Garansi</label>
        <input id="warranty_expiry" type="date" name="warranty_expiry" value="{{ old('warranty_expiry', optional($asset->warranty_expiry ?? null)->format('Y-m-d')) }}"
               class="field-input @error('warranty_expiry') has-error @enderror">
        @error('warranty_expiry') <p class="field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label for="notes" class="field-label">Catatan</label>
    <textarea id="notes" name="notes" rows="4" class="field-input @error('notes') has-error @enderror">{{ old('notes', $asset->notes ?? '') }}</textarea>
    @error('notes') <p class="field-error">{{ $message }}</p> @enderror
</div>