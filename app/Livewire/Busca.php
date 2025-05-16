<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use App\Models\Busca as ModelsBusca;

use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Output\ConsoleOutput;

#use Symfony\Component\Process\Exception\ProcessFailedException;
#use Symfony\Component\Process\Process;

use HeadlessChromium\BrowserFactory;


class Busca extends Component
{


    #[Rule('required|min:3')]
    public $query;

    public $ids = [];

    public function updatedIds($id)
    {
        dd($this->id);
    }

    public function add()
    {
        $this->validate();
        ModelsBusca::create(['q' => $this->query, 'slug' => Str::slug($this->query)]);
        session()->flash('success', 'Query added');

        $this->reset('query');
        #dd($id);
    }

    public function del($id)
    {
        ModelsBusca::find($id)->delete();
        #dd($id);
    }

    public $out;

 
    public function Bot($id)
    {

        $busca_ids = $id;
        $acao = 'search';
        $signature = '/usr/bin/php artisan busca '.$busca_ids.' --acao='.$acao;

        $res = Process::path("/var/www/dr")->timeout(0)->run($signature);

        #dd($res->output());
        $this->out = $signature."\n\n".$res->output();

        $this->stream(
            to: 'out',
            content: $this->out,
            replace: true,
        );

        return $this->out;

        #dd($id);
    }



    #[Computed()]
    public function getAll()
    {
        return ModelsBusca::all();
    }

    #[Layout("layouts/app")]
    public function render()
    {
        return view('livewire.busca', [
            'buscas' => $this->getAll(),
        ]);
    }
}
