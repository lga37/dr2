<?php

namespace App\Livewire;

use Livewire\Component;

use Carbon\Carbon;
use App\Models\Arxiv;
use App\Models\Video;
use App\Models\Comentario;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

class Toxic extends Component
{




    public Video $video;
    public int $video_id;

    public function mount(Video $video)
    {

        $this->video = $video;
        $this->video_id = $video->id;
    }


    #[Layout("layouts/app")]
    public function render(){
         
        $video = $this->video_id;

        $comentarios = Comentario::where('video_id', $this->video_id)
            ->select('id', 'dt', 'tox')
            ->whereNotNull('tox')
            ->orderBy('dt')
            ->get();

        if ($comentarios->isEmpty()) {
            return view('livewire.toxic', ['pontos' => []]);
        }

$inicio = Carbon::parse($comentarios->min('dt'));

$pontos = $comentarios->map(function ($c) use ($inicio) {
    $semana = Carbon::parse($c->dt)->diffInWeeks($inicio); // sempre positivo agora
    return [
        'x' => $semana,
        'y' => round($c->tox * 100, 1),
        'z' => $c->id,
    ];
})->toArray();


        $video = $this->video;

#dd($comentarios->take(5)->toArray(), $pontos);


        return view('livewire.toxic', compact('video', 'pontos'));

        
    }











}
