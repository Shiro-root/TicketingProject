<?php

namespace App\Mail;

use App\Models\ReportSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ScheduledReportMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ReportSchedule $schedule,
        public readonly string $fileContents,
        public readonly string $fileName,
        public readonly string $mimeType,
        public readonly array $summary,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Terjadwal: '.$this->schedule->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.scheduled-report',
            with: [
                'schedule' => $this->schedule,
                'summary' => $this->summary,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->fileContents, $this->fileName)->withMime($this->mimeType),
        ];
    }
}
