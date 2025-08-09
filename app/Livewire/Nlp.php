<?php

namespace App\Livewire;

use Livewire\Component;

use Carbon\Carbon;
use App\Models\Busca;
use App\Models\Comentario;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;


class Nlp extends Component
{


    public Busca $busca;
    public int $busca_id;

    public function mount(Busca $busca)
    {

        $this->busca = $busca;
        $this->busca_id = $busca->id;
    }


    #[Layout("layouts/app")]
    public function render(){
         
        $busca = $this->busca_id;

        
        
        $pontos = [];

        return view('livewire.nlp', compact('busca', 'pontos'));

        
    }
}
