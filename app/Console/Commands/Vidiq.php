<?php

namespace App\Console\Commands;

use App\Models\Canal;
use App\Models\Video;
use Illuminate\Support\Str;
use HeadlessChromium\Dom\Node;
use Illuminate\Console\Command;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\ElementNotFoundException;

class Vidiq extends Bot
{

    protected $signature = 'vidiq {vidiq_ids?*} {--acao=craw}';


    public function craw(array $vidiq_ids)
    {


        $queries = Canal::whereIn('id', $vidiq_ids)->select('id', 'youtube_id')->get()->toArray();

        #dd($queries);
        foreach ($queries as $query) {
            $youtube_id = $query['youtube_id'];
            $canal_id = $query['id'];
  
            $url = "https://vidiq.com/youtube-stats/channel/$youtube_id/";
            echo "\n" . $url;

            try {
    
                $this->initBrowser(true);
                $page = $this->browser->createPage();
                $page->navigate($url);
    
                sleep(5);
    
                $dt = $local = $categ = $videos = $inscritos = $score = $views = $min = $max = $engagement = $frequency = $length = null;
    
                $re_esqs = [
                    'dt' => 'Joined<\/p><\/div><p class="mb-0 text-right text-white">(.+) (\d{1,2}), (\d{4})<\/p><\/div>',
                    'local' => 'Location<\/p><\/div><p class="mb-0 text-right text-white">(.+?)<\/p><\/div>',
                    'categ' => 'Category<\/p><\/div><p class="mb-0 text-right text-white">(.+?)<\/p><\/div>',
                    'videos' => 'Videos<\/p><\/div><p class="mb-0 text-right text-white">(.+?)<\/p><\/div>',
                    'inscritos' => 'Subscribers<\/p><\/div><p class="mb-0 text-right text-white">(.+?)<\/p><\/div>',
                ];
    
    
                $re_dirs = [
                    'score' => 'Overall Score:<\/p><p class.+>(\w{1})<\/span><\/p>',
                    'views' => 'Video Views:<\/p><p .+">([\d\.KMB]+)<\/p>',
                    'min_max' => '\$<!-- -->([\d\.KM]+)<!-- --> - \$<!-- -->([\d\.KM]+)<\/p>',
                    'frequency' => 'Video Upload Frequency:<\/p>.+>([\d\.]+)<!-- --> \/ <!-- -->week<\/p>',
                    'length' => 'Average Video Length:<\/p>.+>([\d\.]+)<!-- --> <!-- -->minutes<\/p>',
                    'engagement' => 'Engagement Rate:<\/p>.+>([\d\.]+)<!-- -->%<\/p>',
                ];
    
                $seletor_esq = 'body > main > section > div > div.mx-auto.max-w-\[1172px\].overflow-x-hidden.px-4 > div.flex.flex-col.items-start.justify-between.gap-4.lg\:flex-row.lg\:gap-6 > div.order-2.flex.w-full.flex-col.gap-4.lg\:order-1.lg\:w-1\/4.lg\:gap-6';
                $seletor_dir = 'body > main > section > div > div.mx-auto.max-w-\[1172px\].overflow-x-hidden.px-4 > div.flex.flex-col.items-start.justify-between.gap-4.lg\:flex-row.lg\:gap-6 > div.order-1.flex.w-full.flex-col.gap-4.lg\:w-\[calc\(75\%-24px\)\].lg\:gap-6 > div:nth-child(1)';
    
                $esq = $page->dom()->querySelector($seletor_esq);
    
                #dd($esq);
                if ($esq instanceof Node) {
                    $esq = $esq->getHtml();
                    $esq = limpaEspacosAcentuacao($esq);
                    foreach ($re_esqs as $key => $re) {
                        if (preg_match('/' . $re . '/', $esq, $res)) {
                            if ($key == 'dt') {
                                $mes = $res[1];
                                $mes = retornaMes($mes);
                                $dia = $res[2];
                                $ano = $res[3];
                                $dt = $ano . '-' . $mes . '-' . $dia;
                            } else {
                                $$key = $res[1];
                            }
                        }
                    }
                } else {
                    echo "erro no crawling";
                }
    
                $dir = $page->dom()->querySelector($seletor_dir);
    
                if ($dir instanceof Node) {
                    $dir = $dir->getHtml();
                    $dir = limpaEspacosAcentuacao($dir);
                    foreach ($re_dirs as $key => $re) {
                        if (preg_match('/' . $re . '/', $dir, $res)) {
                            if ($key == 'min_max') {
                                $min = $res[1];
                                $max = $res[2];
                            } else {
                                $$key = $res[1];
                            }
                        }
                    }
                } else {
                    echo "erro no crawling";
                }
    
                $videos = return_kmb_to_integer($videos);
                $inscritos = return_kmb_to_integer($inscritos);
                $views = return_kmb_to_integer($views);
    
                $min = return_kmb_to_integer($min);
                $max = return_kmb_to_integer($max);
                $length = return_kmb_to_integer($length);
    
                #$categ = urldecode($categ);
    
    
                $campos = compact('dt', 'local', 'categ', 'videos', 'inscritos', 'score', 'views', 'min', 'max', 'engagement', 'frequency', 'length');
    
                #dd($campos);
    
                $canal = Canal::findOrFail($canal_id);
                $res = $canal->update($campos);
                echo "\n---------- canal $canal_id atualizado com " . $res ? 'sucesso' : 'erro';
    
                #dump($canal);
            } catch (OperationTimedOut $e) {
                echo '::::1111' . $e->getMessage();
            } catch (ElementNotFoundException $e) {
                echo '::::33333' . $e->getMessage();
            } catch (\Exception $e) {
                echo '::::22222 ' . dump($e);
            } finally {
    
                $this->closeBrowser();
            }            

            
        }
    }


    public function handle()
    {

        $vidiq_ids = $this->argument('vidiq_ids');
        $acao = $this->option('acao');


        if (method_exists($this, $acao)) {
            #echo "method exists";
            $this->{$acao}($vidiq_ids);
        } else {
            echo "Este metodo nao existe";
        }


        #$this->info('Processado com sucesso!');

        return self::SUCCESS;
    }
}
