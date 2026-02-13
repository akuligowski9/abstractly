<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SourceController;
use App\Livewire\DisciplinePicker;
use App\Livewire\SourcePicker;
use App\Livewire\DigestViewer;
use App\Livewire\SavedPapers;

Route::get('/', fn() => redirect()->route('disciplines.index'));

Route::get('/disciplines', DisciplinePicker::class)->name('disciplines.index');
Route::get('/disciplines/{slug}', SourcePicker::class)->name('disciplines.show');

Route::get('/disciplines/{slug}/sources/{key}/preview', [SourceController::class, 'preview'])->name('sources.preview');

Route::get('/digest', DigestViewer::class)->name('digest.show');

Route::get('/saved', SavedPapers::class)->name('saved.index');
