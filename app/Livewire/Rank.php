<?php

namespace App\Livewire;

use App\Models\Video;
use Livewire\Attributes\Layout;
use LivewireUI\Modal\ModalComponent;

class Rank extends ModalComponent
{

    // Rank.php (componente do modal)
    public $ranking = [];

    public function mount(Video $video)
    {
        dd($video);
        #$this->ranking = json_decode($ranking, true);
    }

    function ranking($id)
    {
        $video = Video::find($id);
        $txt = $video->caption;

        #dump($txt);

        $txt = strtolower($txt);
        $txt = $this->limpaStr2BD($txt);

        #dump($txt);

        $ranking = $this->rnk($txt);

        // $this->dispatch('openModal', [
        //     'component' => 'rank',
        //     'arguments' => [
        //         'ranking' => json_encode($ranking) // <-- importante!
        //     ]
        // ]);
        $this->ranking = json_decode($ranking, true);

    }


    function rnk($txt)
    {
        #$tudo = implode(" ", $txt);
        $parts = explode(" ", $txt);
        #dd($parts);
        $uniqueWords = array_unique($parts);
        $uniqueWordCounts = array_count_values($parts);
        #$density = $frequency / count ($parts) * 100;
        #print_r($uniqueWordCounts);
        arsort($uniqueWordCounts, SORT_NUMERIC);
        #print_r($uniqueWordCounts);

        
        $preposicoes = ['a', 'ante', 'apos', 'ate', 'com', 'contra', 'de', 'desde', 'em', 'entre', 'para', 'per', 'perante', 'por', 'sem', 'sob', 'sobre', 'tras', 'afora', 'como', 'conforme', 'consoante', 'durante', 'exceto', 'mediante', 'menos', 'salvo', 'segundo', 'visto',];

        $artigos = ['o', 'a', 'os', 'as', 'um', 'uma', 'uns', 'uma', 'meu', 'minha', 'meus', 'minhas', 'teu', 'tua', 'teus', 'tuas', 'seu', 'sua', 'seus', 'suas', 'nosso', 'nossa', 'nossos', 'nossas', 'vosso', 'vossa', 'vossos', 'vossas', 'este', 'esta', 'isto', 'esse', 'essa', 'isso', 'aquele', 'aquela', 'aquilo', 'neste', 'nesta', 'nisto', 'nesse', 'nessa', 'nisso', 'naquele', 'naquela', 'naquilo',];

        $pronomes = ['eu', 'mim', 'me', 'comigo', 'tu', 'te', 'ti', 'contigo', 'ele', 'se', 'o', 'a', 'lhe', 'si', 'consigo', 'nos', 'conosco', 'vos', 'convosco', 'eles', 'si', 'os', 'as', 'lhes', 'se', 'consigo', 'meu', 'minha', 'meus', 'minhas', 'tu', 'teu', 'tua', 'teus', 'tuas', 'ele', 'seu', 'seus', 'sua', 'suas', 'nos', 'nosso', 'nossos', 'nossa', 'nossas', 'vos', 'vossa', 'vosso', 'vossos', 'vossas', 'eles', 'seu', 'sua', 'seus', 'suas', 'isto', 'este', 'esta', 'estes', 'estas', 'isso', 'esse', 'essa', 'esses', 'essas', 'aquilo', 'aquele', 'aquela', 'aqueles', 'aquelas', 'algum', 'alguma', 'alguns', 'algumas', 'nenhum', 'nenhuma', 'nenhuns', 'nenhumas', 'todo', 'toda', 'todos', 'todas', 'outro', 'outra', 'outros', 'outras', 'muito', 'muita', 'muitos', 'muitas', 'pouco', 'pouca', 'poucos', 'poucas', 'certo', 'certa', 'certos', 'certas', 'vario', 'varia', 'varios', 'varias', 'quanto', 'quanta', 'quantos', 'quantas', 'tanto', 'tanta', 'tantos', 'tantas', 'alguem', 'ninguem', 'quem', 'outrem', 'algo', 'tudo', 'nada', 'cada', 'mais', 'menos', 'algum', 'nenhum', 'todo', 'outro', 'muito', 'pouco', 'certo', 'varios', 'tanto', 'quanto', 'qualquer', 'alguem', 'ninguem', 'tudo', 'outrem', 'nada', 'quem', 'cada', 'algo',];

        $conjuncao = ['e', 'ou', 'mas', 'pois', 'como', 'que', 'se', 'quando', 'onde', 'ate', 'embora', 'caso', 'afinal', 'porque', 'ja', 'logo',];

        $interjeicao = ['ai', 'ola', 'ui', 'ixi', 'oba', 'eita', 'ufa', 'viva', 'nossa', 'socorro', 'puxa', 'caramba', 'haja', 'xiii', 'ah', 'e', 'querida',];

        $numerais_cardinais = ['um', 'dois', 'tres', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove', 'dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove', 'vinte', 'trinta', 'quarenta', 'cinqüenta', 'sessenta', 'setenta', 'oitenta', 'noventa', 'cem', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos', 'mil'];

        $multiplicativos = ['dobro', 'triplo', 'quadruplo',];

        $ordinais = ['primeiro', 'segundo', 'terceiro',];

        $outros = ['nao','dos','enquanto','voce','ainda','pra','pela','entao','numa','seja','ela','dele','dela','qual','disso','desse','dessa','elas','nas','das','dos','tao','mesmo','quais','desses','deles','delas','uns','umas'];

        $excluir = array_merge($preposicoes,$artigos,$pronomes,$conjuncao,$interjeicao,$numerais_cardinais,$multiplicativos,$ordinais,$outros);
        #$excluir = array_unique($todos);

        #dump($excluir);

        foreach ($uniqueWordCounts as $w => $qty) {
            if (strlen($w) >= 3) {
                if (!in_array($w, $excluir)) {
                    $ranking[$w] = $qty;
                }
            }
        }

        $tot = array_sum($ranking);
        return $ranking;
      
    }



    function limpaStr2BD($str)
    {

        $permitidos1 = [32]; #espaco
        $permitidos2 = range(48, 57); #0-9
        $permitidos3 = range(65, 90); #A-Z
        $permitidos4 = range(97, 122); #a-z
        $permit = array_merge($permitidos1, $permitidos2, $permitidos3, $permitidos4);

        $str_nova = "";

        for ($i = 0; $i < strlen($str); $i++) {
            if (in_array(ord($str[$i]), $permit)) {
                $str_nova .= $str[$i];
            }
        }

        return $str_nova;
    }

    #[Layout("layouts/app")]
    public function render()
    {

        $ranking = $this->ranking;
        return view('livewire.rank',compact('ranking'));
    }
}
