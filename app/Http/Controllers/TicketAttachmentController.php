<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAttachmentController extends Controller
{
    public function download(Request $request, Ticket $ticket, TicketAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $ticket);
        abort_if($attachment->ticket_id !== $ticket->id, 404);

        return Storage::disk($attachment->disk)->download($attachment->file_path, $attachment->original_name);
    }

    public function destroy(Request $request, Ticket $ticket, TicketAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $ticket);
        abort_if($attachment->ticket_id !== $ticket->id, 404);

        abort_unless(
            $attachment->uploaded_by === $request->user()->id || $request->user()->hasPermission('ticket.update'),
            403,
            'Anda tidak memiliki izin menghapus lampiran ini.'
        );

        Storage::disk($attachment->disk)->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', 'attachment-deleted');
    }
}
