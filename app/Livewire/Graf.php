<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Arxiv;
use App\Models\Canal;
use App\Models\Comentario;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;


class Graf extends Component
{


    public Canal $canal;
    public int $canal_id;

    public function mount(Canal $canal)
    {
        $this->canal = $canal;
        $this->canal_id = $canal->id;
    }


    #[Layout("layouts/app")]
    public function render(){
         
        #dd($this->canal_id);

        $dados = DB::table('arxivs')
            ->select(
                DB::raw("YEAR(ts) as ano"),
                DB::raw("WEEK(ts, 1) as semana"),
                DB::raw("MAX(subscribers) as inscritos")
            )
            ->where('canal_id', $this->canal_id)
            ->whereNotNull('subscribers')
            ->groupBy('ano', 'semana')
            ->orderBy('ano')
            ->orderBy('semana')
            ->get();

        $max = $dados->max('inscritos');

        $hoje = Carbon::now();
        $pontos = Arxiv::where('parsed', 1)
            ->where('canal_id', $this->canal_id)
            ->select('id','views','ts', 'subscribers')
            ->orderBy('ts')
            ->get()
            ->map(function ($arx) use ($hoje) {
                $diff_in_weeks = round(Carbon::parse($arx->ts)->diffInWeeks($hoje));
                return (object)[
                    #'ts' => $arx->ts->format('Y-m-d H:i'),
                    'ts' => Carbon::parse($arx->ts)->format('Y-m-d H:i'),

                    'id' => $arx->id,
                    'url' => $arx->url,
                    'views' => $arx->views,
                    'subscribers' => $arx->subscribers,
                    'diff_weeks' => $diff_in_weeks,
                ];
            });

        #dump($dados);

        $canal = $this->canal;

        return view('livewire.arxiv', compact('canal','dados', 'pontos', 'max'));

        
    }




    // #[Layout("layouts/app")]
    // public function render3333333()
    // {

    //     $hoje = Carbon::now();
    //     // $arxivs = ModelsArxiv::where('parsed', 1)->where('canal_id', $this->canal_id)->select('ts', 'subscribers')->orderBy('ts')
    //     //     ->get()->map(function ($arx) use ($hoje) {
    //     //         $ts = $arx->ts;

    //     //         $endDate = Carbon::parse($ts);
    //     //         $diff_in_weeks =  round($endDate->diffInWeeks($hoje));

    //     //         return new Point(y: $arx->subscribers, x: $diff_in_weeks);
    //     //     })->toArray();


    //     $res = Comentario::whereIn('video_id', [4, 5])
    //         ->whereNotNull('tox')
    //         ->select('tox', 'video_id', 'dt')
    //         ->orderBy('dt','DESC')
    //         ->get()
    //         ->map(function($comm) use ($hoje){
    //             $t = $comm->dt;
    //             $endDate = Carbon::parse($t);
    //             $diff_in_weeks =  round($endDate->diffInWeeks($hoje));
    //             return ['semana'=>$diff_in_weeks,'t'=>$t, 'tox'=>$comm->tox];

    //         })
    //         // ->groupBy(function ($val) {
    //         //     return Carbon::parse($val->ts)->format('m/Y');
    //         // })

    //         ->toArray();

    //     #dd($res);



    //     $res = Arxiv::where('canal_id', 5)->where('parsed', 1)
    //         ->orderBy('ts')
    //         ->select('ts', 'subscribers')
    //         ->get()
    //         ->groupBy(function ($val) {
    //             return Carbon::parse($val->ts)->format('m/Y');
    //         })

    //         ->toArray();

    //     #dd($res);

    //     return view('livewire.graf', ['canal' => $this->canal]);
    // }

}
