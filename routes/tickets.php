<?php

use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('users/lookup', [UserLookupController::class, 'search'])->name('users.lookup');

    Route::post('tickets/trashed/{id}/restore', [TicketController::class, 'restore'])->name('tickets.restore');
    Route::get('tickets/trashed/list', [TicketController::class, 'trashed'])->name('tickets.trashed');
    Route::post('tickets/check-duplicates', [TicketController::class, 'checkDuplicates'])->name('tickets.check-duplicates');

    // Bonus Feature: Bulk Action — satu aksi untuk banyak ticket sekaligus dari halaman index.
    Route::post('tickets/bulk', [TicketController::class, 'bulkAction'])->name('tickets.bulk');

    Route::resource('tickets', TicketController::class);

    Route::prefix('tickets/{ticket}')->name('tickets.')->group(function () {
        Route::post('assign', [TicketController::class, 'assign'])->name('assign');
        Route::post('accept', [TicketController::class, 'accept'])->name('accept');
        Route::patch('status', [TicketController::class, 'transitionStatus'])->name('status');
        Route::post('close', [TicketController::class, 'close'])->name('close');
        Route::post('reopen', [TicketController::class, 'reopen'])->name('reopen');
        Route::post('archive', [TicketController::class, 'archive'])->name('archive');
        Route::post('duplicate', [TicketController::class, 'duplicate'])->name('duplicate');
        Route::post('merge', [TicketController::class, 'merge'])->name('merge');
        Route::post('rate', [TicketController::class, 'rate'])->name('rate');
        Route::post('watch', [TicketController::class, 'toggleWatch'])->name('watch');
        Route::post('bookmark', [TicketController::class, 'toggleBookmark'])->name('bookmark');

        Route::post('comments', [TicketCommentController::class, 'store'])->name('comments.store');
        Route::patch('comments/{comment}', [TicketCommentController::class, 'update'])->name('comments.update');
        Route::delete('comments/{comment}', [TicketCommentController::class, 'destroy'])->name('comments.destroy');

        Route::get('attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])->name('attachments.destroy');
    });
});
