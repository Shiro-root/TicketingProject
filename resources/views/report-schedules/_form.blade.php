@php($schedule = $schedule ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
    <div>
        <label for="name" class="field-label">Nama Jadwal</label>
        <input id="name" type="text" name="name" value="{{ old('name', $schedule->name ?? '') }}" required
               placeholder="mis. Laporan Mingguan Divisi IT"
               class="field-input @error('name') has-error @enderror">
        @error('name') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="frequency" class="field-label">Frekuensi</label>
        <select id="frequency" name="frequency" required class="field-input @error('frequency') has-error @enderror">
            <option value="daily" @selected(old('frequency', $schedule->frequency ?? '') === 'daily')>Harian</option>
            <option value="weekly" @selected(old('frequency', $schedule->frequency ?? 'weekly') === 'weekly')>Mingguan</option>
            <option value="monthly" @selected(old('frequency', $schedule->frequency ?? '') === 'monthly')>Bulanan</option>
        </select>
        @error('frequency') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="period_days" class="field-label">Cakupan Data (hari terakhir, opsional)</label>
        <input id="period_days" type="number" min="1" max="365" name="period_days"
               value="{{ old('period_days', $schedule->period_days ?? '') }}"
               placeholder="mis. 7 untuk laporan mingguan"
               class="field-input @error('period_days') has-error @enderror">
        <p class="text-caption-sm text-mute mt-xs">Kosongkan untuk mencakup semua data tanpa batas tanggal.</p>
        @error('period_days') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="format" class="field-label">Format File</label>
        <select id="format" name="format" required class="field-input @error('format') has-error @enderror">
            <option value="pdf" @selected(old('format', $schedule->format ?? 'pdf') === 'pdf')>PDF</option>
            <option value="excel" @selected(old('format', $schedule->format ?? '') === 'excel')>Excel</option>
        </select>
        @error('format') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="department_id" class="field-label">Department (opsional)</label>
        <select id="department_id" name="department_id" class="field-input">
            <option value="">Semua Department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $schedule->department_id ?? '') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="category_id" class="field-label">Kategori (opsional)</label>
        <select id="category_id" name="category_id" class="field-input">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $schedule->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status" class="field-label">Status (opsional)</label>
        <select id="status" name="status" class="field-input">
            <option value="">Semua Status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $schedule->status ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="priority" class="field-label">Prioritas (opsional)</label>
        <select id="priority" name="priority" class="field-input">
            <option value="">Semua Prioritas</option>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->value }}" @selected(old('priority', $schedule->priority ?? '') === $priority->value)>{{ $priority->label() }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label for="recipients" class="field-label">Penerima Email</label>
    <input id="recipients" type="text" name="recipients"
           value="{{ old('recipients', is_array($schedule->recipients ?? null) ? implode(', ', $schedule->recipients) : '') }}"
           required placeholder="pisahkan dengan koma, mis. manager@helpdesk.test, admin@helpdesk.test"
           class="field-input @error('recipients') has-error @enderror">
    @error('recipients') <p class="field-error">{{ $message }}</p> @enderror
</div>

<label class="flex items-center gap-xs text-body-sm text-body select-none">
    <input type="checkbox" name="is_active" value="1"
           @checked(old('is_active', $schedule->is_active ?? true)) class="rounded-sm border-ash text-primary">
    Aktifkan jadwal ini
</label>
