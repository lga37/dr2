<?php

namespace App\Livewire;

use App\Models\Video;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

use Illuminate\Support\Facades\Route;
use App\Models\Comentario as ModelsComentario;

class Comentario extends Component
{

    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $sortDirection = 'ASC';
    public $sortColumn = 'id';
    public int $video_id = 0;


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
        ModelsComentario::find($id)->delete();
        #dd($id);
    }

    public function mount($video_id = 0)
    {
        $this->video_id = $video_id;
    }

    public Video $video;

    function setTox($id)
    {
        $comm = ModelsComentario::find($id);
        $txt = $comm->texto;

        $params = [
            "comment" => ["text" => "hi everybody is fine"],
            "languages" => ["en"],
            "requestedAttributes" => ["TOXICITY"=>""]
        ];
        $params = json_encode($params);

        #dd($params);
        $txt = trim($txt,'"');

        $params = '{comment: {text: "'.$txt.'"},languages: ["pt"],requestedAttributes: {TOXICITY:{}} }';

        $url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . env('PERSPECTIVE_API');

        $headers =  [
            "Content-type: application/json",

        ];

        $result = $this->cUrlGetData($url, $params, $headers);
        $res = json_decode($result, true);


        if (!is_array($res)) {
            return;
        }

        #dd($res);

        $tox = round($res['attributeScores']['TOXICITY']['summaryScore']['value'],3);


        $comm->update(['tox' => $tox]);
    }




    function cUrlGetData($url, $post_fields = null, $headers = null)
    {

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);

        if (!empty($post_fields)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);
        return $data;
    }

    function processaNumOcorrencias($texto, $query_slug)
    {
        if ($texto == '' || $query_slug == '') {
            return;
        }
        #$texto = stripAccents($texto);
        $query_slug_array = explode('-', $query_slug);

        $texto_slug = Str::slug($texto);
        $texto_slug_espaco = preg_replace('/-/', ' ', $texto_slug);

        $nome_array = explode(" ", $texto_slug_espaco);

        $ocorrencias = [];
        foreach ($query_slug_array as $p) {
            $ocorrencias[$p] = count(array_filter($nome_array, function ($n) use ($p) {
                return $n == $p;
            }));
        }
        arsort($ocorrencias);
        $tot = array_sum($ocorrencias);
        return compact('texto_slug_espaco', 'tot', 'ocorrencias');
    }






    #[Layout("layouts/app")]
    public function render()
    {
        #$comentarios = ModelsComentario::all();
        #return view('livewire.comentario',compact('comentarios'));

        if ($this->video_id == 0) {
            $modoBusca = ModelsComentario::search($this->search)->orderBy($this->sortColumn, $this->sortDirection)->paginate($this->perPage);
        } else {
            $modoBusca = ModelsComentario::where('video_id', $this->video_id)->orderBy('dt')->paginate($this->perPage);
        }

        $videos = Video::get();

        return view('livewire.comentario', [
            'comentarios' => $modoBusca,
            'videos' => $videos,
        ]);
    }
}
