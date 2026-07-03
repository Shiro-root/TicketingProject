<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export .xlsx untuk laporan ticket (Modul 8).
 * Dipanggil dari ReportController::exportExcel() dengan koleksi Ticket
 * yang sudah difilter oleh ReportService — export class ini murni presentasi.
 */
class TicketReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private readonly Collection $tickets)
    {
    }

    public function collection(): Collection
    {
        return $this->tickets;
    }

    public function title(): string
    {
        return 'Laporan Ticket';
    }

    public function headings(): array
    {
        return [
            'No. Ticket', 'Judul', 'Kategori', 'Department', 'Prioritas', 'Status',
            'Dibuat oleh', 'Teknisi', 'Dibuat', 'Due', 'Resolved', 'SLA Terlewati', 'Rating',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->ticket_number,
            $ticket->subject,
            $ticket->category?->name ?? '-',
            $ticket->department?->name ?? '-',
            $ticket->priority->label(),
            $ticket->status->label(),
            $ticket->creator?->name ?? '-',
            $ticket->assignee?->name ?? '-',
            $ticket->created_at->format('Y-m-d H:i'),
            $ticket->due_at?->format('Y-m-d H:i') ?? '-',
            $ticket->resolved_at?->format('Y-m-d H:i') ?? '-',
            $ticket->is_sla_breached ? 'Ya' : 'Tidak',
            $ticket->rating ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E60023']]],
        ];
    }
}
