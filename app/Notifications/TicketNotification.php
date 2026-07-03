<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\EmailTemplate;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly NotificationType $type,
        public readonly Ticket $ticket,
        public readonly array $data = [],
        public readonly ?User $actor = null,
        public readonly bool $wantsInApp = true,
        public readonly bool $wantsEmail = true,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->wantsInApp) {
            $channels[] = 'database';
        }

        if ($this->wantsEmail && $this->type->emailTemplateKey()) {
            $channels[] = 'mail';
        }

        return $channels ?: ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type->value,
            'icon' => $this->type->icon(),
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'message' => $this->message(),
            'actor' => $this->actor?->name,
            'url' => route('tickets.show', $this->ticket),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $template = EmailTemplate::where('key', $this->type->emailTemplateKey())
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return (new MailMessage)
                ->subject('['.$this->ticket->ticket_number.'] '.$this->type->label())
                ->line($this->message())
                ->action('Lihat Ticket', route('tickets.show', $this->ticket));
        }

        $rendered = $template->render(array_merge([
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'user_name' => $notifiable->name,
            'technician_name' => $notifiable->name,
            'status' => $this->ticket->status->label(),
            'commenter_name' => $this->actor?->name ?? 'Sistem',
            'comment_body' => $this->data['comment_body'] ?? '',
            'time_remaining' => $this->data['time_remaining'] ?? '-',
        ], $this->data));

        $plainBody = trim(strip_tags(str_replace(
            ['<p>', '</p>', '<br>', '<blockquote>', '</blockquote>'],
            ['', "\n", "\n", '"', '"'],
            $rendered['body']
        )));

        return (new MailMessage)
            ->subject($rendered['subject'])
            ->greeting('Halo '.$notifiable->name.',')
            ->line($plainBody)
            ->action('Lihat Ticket', route('tickets.show', $this->ticket));
    }

    private function message(): string
    {
        $actorName = $this->actor?->name ?? 'Sistem';

        return match ($this->type) {
            NotificationType::TICKET_CREATED => "Ticket #{$this->ticket->ticket_number} berhasil dibuat.",
            NotificationType::TICKET_ASSIGNED => "{$actorName} menugaskan ticket #{$this->ticket->ticket_number} kepada Anda.",
            NotificationType::TICKET_UPDATED => "{$actorName} memperbarui ticket #{$this->ticket->ticket_number}.",
            NotificationType::STATUS_CHANGED => "Status ticket #{$this->ticket->ticket_number} berubah dari {$this->data['from']} menjadi {$this->data['to']}.",
            NotificationType::NEW_COMMENT => "{$actorName} menambahkan komentar pada ticket #{$this->ticket->ticket_number}.",
            NotificationType::SLA_WARNING => "Ticket #{$this->ticket->ticket_number} akan melewati SLA dalam {$this->data['time_remaining']}.",
            NotificationType::SLA_BREACHED => "Ticket #{$this->ticket->ticket_number} telah melewati batas SLA — dieskalasi otomatis.",
            NotificationType::APPROVAL_REQUESTED => "{$actorName} meminta persetujuan Anda pada ticket #{$this->ticket->ticket_number}.",
            NotificationType::MENTIONED => "{$actorName} menyebut Anda pada komentar ticket #{$this->ticket->ticket_number}.",
        };
    }
}