<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\TransitionStatusRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketCommentService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketController extends Controller
{
    /** Query-string yang dianggap "filter aktif" — dipakai untuk cek apakah perlu redirect ke Saved Filter default. */
    private const FILTER_KEYS = [
        'search', 'status', 'priority', 'department_id', 'category_id',
        'assigned_to', 'sla_breached', 'date_from', 'date_to', 'show_archived',
    ];

    public function __construct(
        private readonly TicketService $tickets,
        private readonly TicketCommentService $comments,
    ) {
    }

    /** Daftar ticket + filter (status, prioritas, kategori, teknisi, department, SLA, tanggal) + search. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();

        // Bonus Feature: Saved Filter — kalau user buka halaman ini tanpa filter apapun
        // dan punya filter default tersimpan, terapkan otomatis.
        $savedFilters = $user->savedFilters()->latest()->get();

        if (! $request->hasAny(self::FILTER_KEYS)) {
            $default = $savedFilters->firstWhere('is_default', true);
            if ($default && ! empty($default->filters)) {
                return redirect()->route('tickets.index', $default->filters);
            }
        }

        $query = Ticket::query()->with(['category', 'assignee', 'creator', 'department']);

        if (! $user->hasPermission('ticket.view_all')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhereHas('technicians', fn ($t) => $t->where('user_id', $user->id));
            });
        }

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('ticket_number', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%")
                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('assignee', fn ($a) => $a->where('name', 'like', "%{$term}%"));
            });
        }

        foreach (['status', 'priority', 'department_id', 'category_id', 'assigned_to'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->boolean('sla_breached')) {
            $query->where('is_sla_breached', true);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        if (! $request->boolean('show_archived')) {
            $query->notArchived();
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'savedFilters' => $savedFilters,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'technicians' => User::whereHas('role', fn ($r) => $r->whereIn('slug', ['technician', 'supervisor']))->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Ticket::class);

        return view('tickets.create', [
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'assets' => Asset::orderBy('name')->get(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = $this->tickets->create($request->validated(), $request->user(), $request);

        return redirect()->route('tickets.show', $ticket)->with('status', 'ticket-created');
    }

    /** AJAX: deteksi kemungkinan ticket duplikat saat user mengetik judul (bonus feature). */
    public function checkDuplicates(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['subject' => ['required', 'string', 'min:8']]);

        $duplicates = $this->tickets->findPossibleDuplicates($request->string('subject'), $request->user()->id);

        return response()->json([
            'has_duplicates' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates->map(fn ($t) => [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'subject' => $t->subject,
                'url' => route('tickets.show', $t),
            ])->values(),
        ]);
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['category', 'department', 'sla', 'assignee', 'creator', 'technicians', 'tags', 'assets', 'attachments', 'activities.user']);

        return view('tickets.show', [
            'ticket' => $ticket,
            'comments' => $this->comments->visibleFor($ticket, $request->user()),
            'technicians' => User::whereHas('role', fn ($r) => $r->whereIn('slug', ['technician', 'supervisor']))->orderBy('name')->get(),
            'isWatching' => $ticket->watchers()->where('user_id', $request->user()->id)->exists(),
            'isBookmarked' => $ticket->bookmarkedBy()->where('user_id', $request->user()->id)->exists(),
        ]);
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        return view('tickets.edit', [
            'ticket' => $ticket->load(['tags', 'assets']),
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'assets' => Asset::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->update($ticket, $request->validated(), $request->user(), $request);

        return redirect()->route('tickets.show', $ticket)->with('status', 'ticket-updated');
    }

    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);
        $this->tickets->delete($ticket, $request->user(), $request);

        return redirect()->route('tickets.index')->with('status', 'ticket-deleted');
    }

    public function trashed(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = Ticket::onlyTrashed()
            ->with(['category', 'creator'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('tickets.trashed', ['tickets' => $tickets]);
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $ticket = Ticket::withTrashed()->findOrFail($id);
        $this->authorize('restore', $ticket);
        $this->tickets->restore($ticket, $request->user(), $request);

        return redirect()->route('tickets.show', $ticket)->with('status', 'ticket-restored');
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('assign', $ticket);
        $technician = User::findOrFail($request->validated('technician_id'));
        $this->tickets->assign($ticket, $technician, $request->user(), $request, $request->boolean('is_lead', true));

        return back()->with('status', 'ticket-assigned');
    }

    public function accept(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->accept($ticket, $request->user());

        return back()->with('status', 'ticket-accepted');
    }

    public function transitionStatus(TransitionStatusRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->transitionStatus($ticket, TicketStatus::from($request->validated('status')), $request->user());

        return back()->with('status', 'ticket-status-updated');
    }

    public function close(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('close', $ticket);
        $this->tickets->close($ticket, $request->user());

        return back()->with('status', 'ticket-closed');
    }

    public function reopen(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('reopen', $ticket);
        $this->tickets->reopen($ticket, $request->user());

        return back()->with('status', 'ticket-reopened');
    }

    public function archive(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('archive', $ticket);
        $this->tickets->archive($ticket, $request->user());

        return back()->with('status', 'ticket-archived');
    }

    public function duplicate(Request $request, Ticket $ticket): RedirectResponse
    {
        $copy = $this->tickets->duplicate($ticket, $request->user(), $request);

        return redirect()->route('tickets.show', $copy)->with('status', 'ticket-duplicated');
    }

    public function merge(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('merge', $ticket);
        $request->validate(['into_id' => ['required', 'exists:tickets,id', 'different:ticket']]);
        $into = Ticket::findOrFail($request->input('into_id'));
        $this->tickets->merge($ticket, $into, $request->user(), $request);

        return redirect()->route('tickets.show', $into)->with('status', 'ticket-merged');
    }

    public function rate(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('rate', $ticket);
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->tickets->rate($ticket, (int) $request->input('rating'), $request->input('feedback'), $request->user());

        return back()->with('status', 'ticket-rated');
    }

    public function toggleWatch(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->toggleWatcher($ticket, $request->user());

        return back()->with('status', 'watch-toggled');
    }

    public function toggleBookmark(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->toggleBookmark($ticket, $request->user());

        return back()->with('status', 'bookmark-toggled');
    }

    /**
     * Bonus Feature: Bulk Action.
     * Menjalankan satu aksi (status / assign / archive / delete) ke banyak ticket
     * sekaligus. Setiap ticket TETAP melalui pengecekan Policy dan method
     * TicketService yang sama seperti aksi single-ticket — jadi activity timeline,
     * notifikasi, dan audit log tetap tercatat normal per ticket. Ticket yang tidak
     * lolos Policy dilewati (skip), bukan menggagalkan seluruh batch.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tickets,id'],
            'bulk_action' => ['required', 'in:status,assign,archive,delete'],
            'status' => ['required_if:bulk_action,status', 'nullable', 'in:'.implode(',', TicketStatus::values())],
            'technician_id' => ['required_if:bulk_action,assign', 'nullable', 'exists:users,id'],
        ]);

        $gate = Gate::forUser($request->user());
        $action = $data['bulk_action'];
        $technician = $action === 'assign' ? User::findOrFail($data['technician_id']) : null;

        $success = 0;
        $skipped = 0;

        foreach (Ticket::whereIn('id', $data['ids'])->get() as $ticket) {
            $allowed = match ($action) {
                'status' => $gate->allows('update', $ticket),
                'assign' => $gate->allows('assign', $ticket),
                'archive' => $gate->allows('archive', $ticket),
                'delete' => $gate->allows('delete', $ticket),
            };

            if (! $allowed) {
                $skipped++;

                continue;
            }

            try {
                match ($action) {
                    'status' => $this->tickets->transitionStatus($ticket, TicketStatus::from($data['status']), $request->user()),
                    'assign' => $this->tickets->assign($ticket, $technician, $request->user(), $request),
                    'archive' => $this->tickets->archive($ticket, $request->user()),
                    'delete' => $this->tickets->delete($ticket, $request->user(), $request),
                };
                $success++;
            } catch (\Throwable $e) {
                // Mis. transisi status tidak valid untuk ticket tertentu (lihat TicketStatus::allowedTransitions).
                $skipped++;
            }
        }

        return back()->with('status', 'bulk-action-done')
            ->with('bulk_summary', "Bulk action selesai: {$success} ticket berhasil, {$skipped} dilewati (tidak diizinkan/tidak valid).");
    }
}
