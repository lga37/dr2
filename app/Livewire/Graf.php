<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Arxiv;
use App\Models\Canal;
use App\Models\Comentario;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Graf extends Component
{


    public Canal $canal;
    public function mount(Canal $canal)
    {
        $this->canal = $canal;
    }







    #[Layout("layouts/app")]
    public function render()
    {

        $hoje = Carbon::now();
        // $arxivs = ModelsArxiv::where('parsed', 1)->where('canal_id', $this->canal_id)->select('ts', 'subscribers')->orderBy('ts')
        //     ->get()->map(function ($arx) use ($hoje) {
        //         $ts = $arx->ts;

        //         $endDate = Carbon::parse($ts);
        //         $diff_in_weeks =  round($endDate->diffInWeeks($hoje));

        //         return new Point(y: $arx->subscribers, x: $diff_in_weeks);
        //     })->toArray();







        $res = Comentario::whereIn('video_id', [4, 5])
            ->whereNotNull('tox')
            ->select('tox', 'video_id', 'dt')
            ->orderBy('dt','DESC')
            ->get()
            ->map(function($comm) use ($hoje){
                $t = $comm->dt;
                $endDate = Carbon::parse($t);
                $diff_in_weeks =  round($endDate->diffInWeeks($hoje));
                return ['semana'=>$diff_in_weeks,'t'=>$t, 'tox'=>$comm->tox];

            })
            // ->groupBy(function ($val) {
            //     return Carbon::parse($val->ts)->format('m/Y');
            // })

            ->toArray();

        dd($res);



        $res = Arxiv::where('canal_id', 5)->where('parsed', 1)
            ->orderBy('ts')
            ->select('ts', 'subscribers')
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->ts)->format('m/Y');
            })

            ->toArray();

        dd($res);




        return view('livewire.graf', ['canal' => $this->canal]);
    }
}
