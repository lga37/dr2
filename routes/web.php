<?php

#use App\Livewire\Monet;
use App\Livewire\Graf;

use App\Livewire\Tarefa1;
use App\Livewire\Tarefa2;
use App\Livewire\Tarefa3;


use App\Livewire\Arxiv;
use App\Livewire\Toxic;
use App\Livewire\Nlp;
use App\Livewire\Busca;
use App\Livewire\Canal;
use App\Livewire\Monet;
use App\Livewire\Video;
use App\Livewire\Vidiq;
use App\Livewire\Comentario;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('busca');
});


Route::get('tarefa1', Tarefa1::class)->name('tarefa1');
Route::get('tarefa2', Tarefa2::class)->name('tarefa2');
Route::get('tarefa3', Tarefa3::class)->name('tarefa3');



Route::get('busca', Busca::class)->name('busca');
Route::get('video', Video::class)->name('video');
Route::get('canal', Canal::class)->name('canal');
Route::get('monet', Monet::class)->name('monet');
Route::get('arxiv/{canal_id?}', Arxiv::class)->name('arxiv');

Route::get('graf/{canal?}', Graf::class)->name('graf');
Route::get('toxic/{video?}', Toxic::class)->name('toxic');
Route::get('nlp/{busca?}', Nlp::class)->name('nlp');

Route::get('comentario/{video_id?}', Comentario::class)->name('comentario');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
