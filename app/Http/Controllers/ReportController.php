<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Exports\TicketReportExport;
use App\Services\AuditLogger;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /** Halaman Report: kartu ringkasan, grafik, tabel preview (maks 50 baris), + tombol export. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\Ticket::class);

        $filters = $this->filters($request);

        return view('reports.index', array_merge([
            'summary' => $this->reports->summary($filters),
            'technicianPerformance' => $this->reports->technicianPerformance($filters),
            'tickets' => $this->reports->rows($filters)->take(50),
            'filters' => $filters,
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
        ], $this->reports->filterOptions()));
    }

    /** Export laporan ke PDF (barryvdh/laravel-dompdf) — seluruh baris hasil filter, tanpa limit 50. */
    public function exportPdf(Request $request): Response
    {
        $filters = $this->filters($request);
        $summary = $this->reports->summary($filters);
        $tickets = $this->reports->rows($filters);

        $pdf = Pdf::loadView('reports.pdf.tickets', [
            'summary' => $summary,
            'tickets' => $tickets,
            'filters' => $filters,
            'generatedAt' => now(),
            'generatedBy' => $request->user(),
        ])->setPaper('a4', 'landscape');

        $this->auditLogger->log(AuditAction::DOWNLOAD_REPORT, $request->user(), $request, null, null, [
            'format' => 'pdf', 'filters' => $filters, 'total_rows' => $tickets->count(),
        ]);

        return $pdf->download('laporan-ticket-'.now()->format('Y-m-d-His').'.pdf');
    }

    /** Export laporan ke Excel (maatwebsite/excel) — seluruh baris hasil filter. */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);
        $tickets = $this->reports->rows($filters);

        $this->auditLogger->log(AuditAction::DOWNLOAD_REPORT, $request->user(), $request, null, null, [
            'format' => 'excel', 'filters' => $filters, 'total_rows' => $tickets->count(),
        ]);

        return Excel::download(new TicketReportExport($tickets), 'laporan-ticket-'.now()->format('Y-m-d-His').'.xlsx');
    }

    /** Ambil & normalisasi query-string filter — dipakai bersama oleh index/exportPdf/exportExcel. */
    private function filters(Request $request): array
    {
        return $request->only([
            'date_from', 'date_to', 'department_id', 'category_id', 'assigned_to', 'status', 'priority',
        ]);
    }
}
