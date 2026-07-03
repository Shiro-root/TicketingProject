<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreCommentRequest;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\TicketCommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    public function __construct(private readonly TicketCommentService $comments)
    {
    }

    public function store(StoreCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        $this->comments->create($ticket, $request->user(), $request->validated(), $request);

        return back()->with('status', 'comment-added')->withFragment('comments');
    }

    public function update(Request $request, Ticket $ticket, TicketComment $comment): RedirectResponse
    {
        abort_if($comment->ticket_id !== $ticket->id, 404);
        abort_unless($comment->user_id === $request->user()->id, 403, 'Anda hanya bisa mengedit komentar sendiri.');

        $request->validate(['body' => ['required', 'string', 'min:1']]);
        $this->comments->update($comment, $request->input('body'), $request->user());

        return back()->with('status', 'comment-updated')->withFragment('comments');
    }

    public function destroy(Request $request, Ticket $ticket, TicketComment $comment): RedirectResponse
    {
        abort_if($comment->ticket_id !== $ticket->id, 404);
        abort_unless(
            $comment->user_id === $request->user()->id || $request->user()->hasPermission('ticket.update'),
            403,
            'Anda tidak memiliki izin menghapus komentar ini.'
        );

        $this->comments->delete($comment);

        return back()->with('status', 'comment-deleted')->withFragment('comments');
    }
}
