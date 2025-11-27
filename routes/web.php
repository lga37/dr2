<?php

#use App\Livewire\Monet;
use App\Livewire\Nlp;

use App\Livewire\Graf;
use App\Livewire\Tese;
use App\Livewire\Arxiv;


use App\Livewire\Busca;
use App\Livewire\Canal;
use App\Livewire\Monet;
use App\Livewire\Toxic;
use App\Livewire\Video;
use App\Livewire\Vidiq;
use Twilio\Rest\Client;
use App\Livewire\Tarefa1;
use App\Livewire\Tarefa2;
use App\Livewire\Tarefa3;
use App\Livewire\Tarefa4;
use App\Models\WaMessage;
use App\Livewire\Comentario;
use App\Livewire\Resultados;
use App\Livewire\Toxicidade;
use Illuminate\Http\Request;
use App\Livewire\Monetizacao;
use App\Livewire\Polarizacao;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Twilio\Security\RequestValidator;
use App\Http\Controllers\ProfileController;


#Auth::loginUsingId(7);

Route::get('/', function () {
    #return redirect()->route('busca');
    return view('home');
})->name('home');




#######################################################################################
#######################################################################################


    Route::get('tarefa1', Tarefa1::class)->name('tarefa1');
    Route::get('tarefa2', Tarefa2::class)->name('tarefa2');
    Route::get('tarefa3', Tarefa3::class)->name('tarefa3');
    Route::get('tarefa4', Tarefa4::class)->name('tarefa4');
    Route::get('resultados', Resultados::class)->name('resultados');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


Route::get('polarizacao', Polarizacao::class)->name('polarizacao');
Route::get('toxicidade', Toxicidade::class)->name('toxicidade');
Route::get('monetizacao', Monetizacao::class)->name('monetizacao');
Route::get('tese', Tese::class)->name('tese');



Route::get('busca', Busca::class)->name('busca');
Route::get('video', Video::class)->name('video');
Route::get('canal', Canal::class)->name('canal');
Route::get('monet', Monet::class)->name('monet');


Route::get('arxiv/{canal_id?}', Arxiv::class)->name('arxiv');

Route::get('graf/{canal?}', Graf::class)->name('graf');
Route::get('toxic/{video?}', Toxic::class)->name('toxic');
Route::get('nlp/{busca?}', Nlp::class)->name('nlp');

Route::get('comentario/{video_id?}', Comentario::class)->name('comentario');



require __DIR__ . '/auth.php';
