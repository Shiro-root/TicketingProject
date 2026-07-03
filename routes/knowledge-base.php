<?php

use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\KnowledgeBaseSuggestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('knowledge-base/suggest', KnowledgeBaseSuggestController::class)->name('knowledge-base.suggest');

    Route::get('knowledge-base/trashed/list', [KnowledgeBaseController::class, 'trashed'])->name('knowledge-base.trashed');
    Route::post('knowledge-base/trashed/{id}/restore', [KnowledgeBaseController::class, 'restore'])->name('knowledge-base.restore');

    Route::resource('knowledge-base', KnowledgeBaseController::class)
        ->parameters(['knowledge-base' => 'article'])
        ->except(['destroy'])
        ->names('knowledge-base');

    Route::delete('knowledge-base/{article}', [KnowledgeBaseController::class, 'destroy'])->name('knowledge-base.destroy');
});