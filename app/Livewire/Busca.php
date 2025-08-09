<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;
use App\Models\Busca as ModelsBusca;
use App\Models\Video;

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

    public function API($busca_id)
    {

        #atencao maximo de 50/request .... nao sei pq 
        $array_id_videoid = Video::where('busca_id',$busca_id)->take(50)->pluck('cod', 'id')->map(function ($cod) {
            #/watch?v=4KzsMcxA6Q8&pp=ygUGYWJvcnRv
            if (preg_match('/[?]{1}v=([^&]+)/', $cod, $m)) {
                $video_id = $m[1];
                return $video_id;
            }
            return false;
        })
            ->reject(function ($value) {
                return $value === false;
            })
            ->toArray();

        $url = "https://www.googleapis.com/youtube/v3/videos";

        $array_videoid_id = array_flip($array_id_videoid);

        #dump($array_videoid_id);

        $videos_id = array_values($array_id_videoid);


        $videos_sep_virgulas = implode(",", $videos_id);

        #dd($videos);

        $params = [
            'order' => 'date',
            'key' => env('YOUTUBE_API_KEY'),
            'part' => 'snippet,statistics,contentDetails',
            #'maxResults' => 100,
            'id' => $videos_sep_virgulas,
            #'pageToken' => $pageToken
        ];

        $call = $url . '?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $call);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        $vs = json_decode($output, true);

        #dump($vs);
        $coluna_vs = $vs['items'];

        #dd($coluna_vs);
        $videos = [];
        $tot = 0;
        foreach ($coluna_vs as $i => $v) {
            $video_id = $v['id'];
            $id_na_tabela_videos = $array_videoid_id[$video_id]; #atencao aqui, to so pegando de volta o id
            extract($v); # snippet,statistics,contentDetails
            $nome = $snippet['title'];
            $desc = $snippet['description'];
            $dt = $snippet['publishedAt'];
            $dt = date('Y-m-d H:i:s', strtotime($dt));
            $lang = $snippet['defaultLanguage'] ?? null;
            $categ_id = $snippet['categoryId'];

            $views = $statistics['viewCount'] ?? null;
            $likes = $statistics['likeCount'] ?? null;
            #$dislikes = $statistics['dislikeCount'] ?? null; #nao tem mais dilikes
            $favorites = $statistics['favoriteCount'] ?? null;
            $comments = $statistics['commentCount'] ?? null;

            $duration = $contentDetails['duration'];
            $duration = ISO8601ToSeconds($duration);
            #$caption = $contentDetails['caption']; #nao vem na api


            $dados = compact('nome', 'desc', 'dt', 'lang', 'categ_id', 'views', 'likes', 'favorites', 'comments', 'duration');
            $videos[$i] = $dados;
            #dump($dados);

            $res = Video::find($id_na_tabela_videos);
            #dump($res);
            if ($atualizou = $res->update($dados)) {
                $tot++;
            }
            #dump($atualizou);
            #dump($res);
            #dump('------------------------');
        }

        if ($tot == count($array_id_videoid)) {
            $msg = "Todos os $tot registros atualizados";
        } else {
            $msg = "Atualizados $tot registros, porem com " . count($array_id_videoid) . " no total";
        }

        #dd($videos);
        session()->flash('success', $msg);
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
