<?php

namespace App\Livewire;

use App\Models\Arxiv;
use App\Models\Canal as ModelsCanal;

use Illuminate\Support\Facades\Process;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Canal extends Component
{
    use WithPagination;


    public $perPage = 10;
    public $search = '';
    public $sortDirection = 'ASC';
    public $sortColumn = 'id';

    public $out;

    public function doSort($column)
    {
        if ($this->sortColumn == $column) {
            $this->sortDirection = ($this->sortDirection == 'ASC') ? 'DESC' : 'ASC';
            return;
        }
        $this->sortColumn = $column;
        $this->sortDirection = 'ASC';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function del($id)
    {
        ModelsCanal::find($id)->delete();
        #dd($id);
    }



    public function clear() {

        $this->reset('out');
    }

    public function Url($id)
    {
        if (is_array($id)) {
            $busca_ids = implode(" ", $id);
        } else {
            $busca_ids = $id;
        }
        $acao = 'craw';
        $signature = '/usr/bin/php artisan canal ' . $busca_ids . ' --acao=' . $acao;

        $res = Process::path("/var/www/dr")->timeout(0)->run($signature);

        #dd($res->output());
        $this->out = $signature . "\n\n" . $res->output();

        $this->stream(
            to: 'out',
            content: $this->out,
            replace: true,
        );

        return $this->out;
    }

    public function graf1() {}



    public function API($ids)
    {

        $ids = is_array($ids) ? $ids : [$ids];

        $queries = ModelsCanal::whereIn('id', $ids)->select('youtube_id')->get()->toArray();



        $youtube_ids = array_column($queries, 'youtube_id');


        $videos_sep_virgulas = implode(",", $youtube_ids);

        $url = "https://www.googleapis.com/youtube/v3/channels";
        #dd($videos);

        $params = [
            'order' => 'date',
            'key' => env('YOUTUBE_API_KEY'),
            'part' => 'snippet,statistics,contentDetails,localizations,topicDetails,contentOwnerDetails',
            'maxResults' => 50,
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

        dd($vs);

        $coluna_vs = $vs['items'];

        $tot = 0;
        foreach ($coluna_vs as $i => $v) {
            $youtube_id = $v['id'];
            extract($v); # snippet,statistics,contentDetails
            $nome = $snippet['title'];
            $desc = limpaEspacosTabs($snippet['description']);
            $dt = $snippet['publishedAt'];
            $dt = date('Y-m-d H:i:s', strtotime($dt));
            $local = $snippet['country'] ?? null;

            $views = $statistics['viewCount'] ?? null;
            $inscritos = $statistics['subscriberCount'] ?? null;
            $views = $statistics['videoCount'] ?? null;

            $dados = compact('nome', 'desc', 'dt', 'local', 'views', 'inscritos', 'views');
            #dump($dados);

            $res = ModelsCanal::where('youtube_id', $youtube_id)->first();
            #dump($res);
            if ($res->update($dados)) {
                $tot++;
            }
            #dump($atualizou);
            #dump($res);
        }

        $msg = "Atualizados $tot registros no total";


        session()->flash('status', $msg);
    }








    public function vidiq($id)
    {
        if (is_array($id)) {
            $busca_ids = implode(" ", $id);
        } else {
            $busca_ids = $id;
        }
        $acao = 'craw';
        $signature = '/usr/bin/php artisan vidiq ' . $busca_ids . ' --acao=' . $acao;

        $res = Process::path("/var/www/dr")->timeout(0)->run($signature);

        #dd($res->output());
        $this->out = $signature . "\n\n" . $res->output();

        $this->stream(
            to: 'out',
            content: $this->out,
            replace: true,
        );

        return $this->out;
    }

    # somente pega os ts no arxiv
    public function arxiv1($id)
    {

        if (is_array($id)) {
            $busca_ids = implode(" ", $id);
        } else {
            $busca_ids = $id;
        }
        $acao = 'craw';
        $signature = '/usr/bin/php artisan arxiv ' . $busca_ids . ' --acao=' . $acao;

        $res = Process::path("/var/www/dr")->timeout(0)->run($signature);

        #dd($res->output());
        $this->out = $signature . "\n\n" . $res->output();

        $this->stream(
            to: 'out',
            content: $this->out,
            replace: true,
        );

        return $this->out;
    }

    # processa os arx para pegar os inscritos
    public function arxiv2($id)
    {

        if (is_array($id)) {
            $busca_ids = implode(" ", $id);
        } else {
            $busca_ids = $id;
        }
        $acao = 'process'; ## aqui muda a f
        $signature = '/usr/bin/php artisan arxiv ' . $busca_ids . ' --acao=' . $acao;

        $res = Process::path("/var/www/dr")->timeout(0)->run($signature);

        #dd($res->output());
        $this->out = $signature . "\n\n" . $res->output();

        $this->stream(
            to: 'out',
            content: $this->out,
            replace: true,
        );

        return $this->out;
    }





    #[Layout("layouts/app")]
    public function render()
    {

        return view('livewire.canal', [
            'canals' => ModelsCanal::search($this->search)
                ->orderBy($this->sortColumn, $this->sortDirection)
                ->paginate($this->perPage),
        ]);
    }
}
