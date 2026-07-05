<?php

namespace App\Console\Commands;

use App\Exports\TicketReportExport;
use App\Mail\ScheduledReportMail;
use App\Models\ReportSchedule;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Bonus Feature: Scheduled Report.
 * Dijadwalkan lewat routes/console.php (setiap jam) — command ini sendiri yang
 * menentukan jadwal mana yang "due" berdasarkan frekuensi (daily/weekly/monthly)
 * dan last_sent_at, jadi cron sebenarnya tidak perlu berjalan lebih sering dari itu.
 */
class SendScheduledReports extends Command
{
    protected $signature = 'reports:send-scheduled';

    protected $description = 'Kirim laporan ticket terjadwal (Bonus Feature: Scheduled Report) via email ke penerima yang terdaftar.';

    public function __construct(private readonly ReportService $reports)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $due = ReportSchedule::where('is_active', true)->get()->filter->isDue();

        if ($due->isEmpty()) {
            $this->info('Tidak ada laporan terjadwal yang jatuh tempo saat ini.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($due as $schedule) {
            $filters = $schedule->toReportFilters();
            $summary = $this->reports->summary($filters);
            $tickets = $this->reports->rows($filters);

            [$contents, $fileName, $mime] = $schedule->format === 'excel'
                ? $this->buildExcel($tickets)
                : $this->buildPdf($schedule, $summary, $tickets);

            foreach ($schedule->recipients as $email) {
                Mail::to($email)->send(new ScheduledReportMail($schedule, $contents, $fileName, $mime, $summary));
            }

            $schedule->update(['last_sent_at' => now()]);
            $sent++;

            $this->info("Terkirim: \"{$schedule->name}\" ke ".count($schedule->recipients).' penerima.');
        }

        $this->info("Selesai — {$sent} jadwal laporan terkirim.");

        return self::SUCCESS;
    }

    private function buildPdf(ReportSchedule $schedule, array $summary, $tickets): array
    {
        $pdf = Pdf::loadView('reports.pdf.tickets', [
            'summary' => $summary,
            'tickets' => $tickets,
            'filters' => $schedule->toReportFilters(),
            'generatedAt' => now(),
            'generatedBy' => $schedule->creator,
        ])->setPaper('a4', 'landscape');

        $fileName = 'laporan-'.str($schedule->name)->slug().'-'.now()->format('Y-m-d').'.pdf';

        return [$pdf->output(), $fileName, 'application/pdf'];
    }

    private function buildExcel($tickets): array
    {
        $contents = Excel::raw(new TicketReportExport($tickets), \Maatwebsite\Excel\Excel::XLSX);
        $fileName = 'laporan-ticket-'.now()->format('Y-m-d').'.xlsx';

        return [$contents, $fileName, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    }
}
