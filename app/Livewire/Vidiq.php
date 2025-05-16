<?php

namespace App\Livewire;

use App\Models\Vidiq as ModelsVidiq;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Vidiq extends Component
{
    #[Layout("layouts/app")]
    public function render()
    {
       
        return view('livewire.vidiq',[
            'vidiqs'=>ModelsVidiq::all(),
        ]);
    }
}
