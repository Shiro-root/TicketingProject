<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\ReportSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bonus Feature: Scheduled Report — CRUD jadwal pengiriman laporan otomatis.
 * Dibatasi permission `report.export` (sama dengan yang dipakai untuk export
 * manual di Tahap 8), karena secara esensi ini adalah "export otomatis berkala".
 */
class ReportScheduleController extends Controller
{
    public function index(): View
    {
        return view('report-schedules.index', [
            'schedules' => ReportSchedule::with(['department', 'category', 'creator'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('report-schedules.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        ReportSchedule::create($data);

        return redirect()->route('report-schedules.index')->with('status', 'report-schedule-created');
    }

    public function edit(ReportSchedule $reportSchedule): View
    {
        return view('report-schedules.edit', array_merge(
            ['schedule' => $reportSchedule],
            $this->formOptions()
        ));
    }

    public function update(Request $request, ReportSchedule $reportSchedule): RedirectResponse
    {
        $reportSchedule->update($this->validated($request));

        return redirect()->route('report-schedules.index')->with('status', 'report-schedule-updated');
    }

    public function destroy(ReportSchedule $reportSchedule): RedirectResponse
    {
        $reportSchedule->delete();

        return redirect()->route('report-schedules.index')->with('status', 'report-schedule-deleted');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'period_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['nullable', 'in:'.implode(',', \App\Enums\TicketStatus::values())],
            'priority' => ['nullable', 'in:'.implode(',', \App\Enums\TicketPriority::values())],
            'format' => ['required', 'in:pdf,excel'],
            'recipients' => ['required', 'string'],
        ]);

        $data['recipients'] = collect(explode(',', $data['recipients']))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values();

        $invalid = $data['recipients']->reject(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        if ($invalid->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'recipients' => 'Format email tidak valid: '.$invalid->implode(', '),
            ]);
        }

        $data['recipients'] = $data['recipients']->all();
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function formOptions(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => \App\Enums\TicketStatus::cases(),
            'priorities' => \App\Enums\TicketPriority::cases(),
        ];
    }
}
