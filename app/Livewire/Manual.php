<?php

namespace App\Livewire;

use App\Models\Arxiv;
use Carbon\Carbon;
use App\Models\Canal;
use Livewire\Component;
use App\Models\Comentario;
use Livewire\Attributes\Layout;
use LivewireUI\Modal\ModalComponent;


class Manual extends ModalComponent
{


    // public function mount()
    // {
    //     dd('aaa');
    //     $res = Arxiv::where('canal_id', 2)->get();
    //         // ->groupBy(function ($val) {
    //         //     return Carbon::parse($val->ts)->format('Y');
    //         // })
    //     #;

    //     dd($res);
    // }

    #[Layout("layouts/app")]
    public function render()
    {



        return view('livewire.manual');
    }
}
