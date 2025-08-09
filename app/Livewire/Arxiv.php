<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Maantje\Charts\Chart;

use Maantje\Charts\Line\Line;

use Maantje\Charts\Line\Lines;
use Maantje\Charts\Line\Point;
use Livewire\Attributes\Layout;
use App\Models\Arxiv as ModelsArxiv;

use Illuminate\Support\Facades\DB;


class Arxiv extends Component
{

    public int $canal_id;
    public function mount($canal_id = 0)
    {
        $this->canal_id = $canal_id;
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
        $pontos = ModelsArxiv::where('parsed', 1)
            ->where('canal_id', $this->canal_id)
            ->select('url','ts', 'subscribers')
            ->orderBy('ts')
            ->get()
            ->map(function ($arx) use ($hoje) {
                $diff_in_weeks = round(Carbon::parse($arx->ts)->diffInWeeks($hoje));
                return (object)[
                    #'ts' => $arx->ts->format('Y-m-d H:i'),
                    'ts' => Carbon::parse($arx->ts)->format('Y-m-d H:i'),

                    'subscribers' => $arx->subscribers,
                    'url' => $arx->url,
                    'diff_weeks' => $diff_in_weeks,
                ];
            });

        dump($dados);


        return view('livewire.arxiv', compact('dados', 'pontos', 'max'));

        
    }




    public function render333333333()
    {
        $hoje = Carbon::now();

        $arxivs = ModelsArxiv::where('parsed', 1)
            ->where('canal_id', $this->canal_id)
            ->select('ts', 'subscribers')->orderBy('ts')
            ->get()->map(function ($arx) use ($hoje) {
                $ts = $arx->ts;

                $endDate = Carbon::parse($ts);

                #$endDate = Carbon::createFromTimestamp($ts);
                $diff_in_weeks =  round($endDate->diffInWeeks($hoje));
                #dump($diff_in_weeks);

                #dd($endDate);



                return new Point(y: $arx->subscribers, x: $diff_in_weeks);
            })->toArray();

      
        #dd($arxivs);

        
        $chart = new Chart(
            series: [
                new Lines(
                    lines: [
                        new Line(
                            points: $arxivs
                            // [
                            //     new Point(y: 0, x: 0),
                            //     new Point(y: 4, x: 100),
                            //     new Point(y: 12, x: 200),
                            //     new Point(y: 8, x: 300),
                            // ]
                            ,
                        ),
                    ],
                ),
            ],
        );

        #echo $chart->render();


        return view('livewire.arxiv', [

            'chart' => $chart,
            'arxivs' => ModelsArxiv::where('parsed', 1)->orderBy('ts')->get(),
        ]);
    }
}
