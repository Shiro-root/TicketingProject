<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * Super Admin selalu bypass lewat Gate::before di AppServiceProvider.
 * Policy ini menangani sisanya: Employee/Guest hanya boleh melihat & mengubah
 * ticket miliknya sendiri; Technician terbatas pada ticket yang ditugaskan.
 */
class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('ticket.view') || $user->hasPermission('ticket.view_all');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasPermission('ticket.view_all')) {
            return true;
        }

        if ($ticket->created_by === $user->id) {
            return true;
        }

        if ($ticket->assigned_to === $user->id) {
            return true;
        }

        if ($ticket->technicians()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $ticket->watchers()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('ticket.create');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->hasPermission('ticket.view_all') && $user->hasPermission('ticket.update')) {
            return true;
        }

        if ($ticket->assigned_to === $user->id || $ticket->technicians()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Employee/Guest boleh edit ticket sendiri selama masih Open.
        return $ticket->created_by === $user->id && $ticket->status->value === 'open';
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.delete');
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.delete');
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.assign');
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.close')
            && ($ticket->assigned_to === $user->id || $user->hasPermission('ticket.view_all'));
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.reopen') || $ticket->created_by === $user->id;
    }

    public function archive(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.archive');
    }

    public function merge(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.merge');
    }

    public function rate(User $user, Ticket $ticket): bool
    {
        return $ticket->created_by === $user->id && $ticket->status->value === 'closed';
    }

    public function addInternalNote(User $user, Ticket $ticket): bool
    {
        return ! $user->hasRole('employee', 'guest');
    }
}
