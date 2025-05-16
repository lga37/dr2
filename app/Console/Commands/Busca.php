<?php

namespace App\Console\Commands;

use App\Models\Busca as BuscaModel;
use App\Models\Video;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use HeadlessChromium\Exception\OperationTimedOut;

class Busca extends Bot
{

    protected $signature = 'busca {busca_ids?*} {--acao=search}';


    public function search(array $busca_ids)
    {

        $queries = BuscaModel::whereIn('id', $busca_ids)->select('q','id')->get()->toArray();

        #dd($queries);
        foreach ($queries as $query) {
            $q = $query['q'];
            $busca_id = $query['id'];

            $url = 'https://www.youtube.com/results?search_query=' . $q;

            #dd($url);

            echo "\n".$url;

            try {

                $this->initBrowser(true);
                $page = $this->browser->createPage();

                #dump('URL: ' . $url);
                $page->navigate($url);

                sleep(1);

                $pags = 4;
                for ($i = 1; $i <= $pags; $i++) {
                    sleep(1);
                    $page->evaluate("window.scrollTo(0, document.body.scrollHeight || document.documentElement.scrollHeight);");

                    $ytds = $page->dom()->querySelectorAll('ytd-video-renderer');
                    #dump(count($ytds));
                }
                $ids = [];
                $re2 = '\/watch\?v=([a-zA-Z0-9_-]+)';

                foreach ($ytds as $ytd) {
                    $html = $ytd->getHtml();
                    $html = limpaEspacosTabs($html);

                    if (preg_match('/' . $re2 . '/', $html, $res)) {
                        #dump($res[1]);
                        #echo $res[1];
                        $ids[] = Str::wrap($res[1], before: 'https://www.youtube.com/watch?v=', after: '');
                    }
                }

                #dump($ids);

                $tot = $err = 0;
                foreach ($ids as $cod) {
                    #dump($cod);
                    $parse = 0;
                    $cod = compact('cod');
                    $v = compact('busca_id','parse');
                    $res = Video::updateOrCreate($cod,$v);

                    $res ? $tot++ : $err++;
                }
                echo "\nTotal upserts para busca_id: $busca_id = $tot - Erros: $err ::::::::::::::";
            } catch (OperationTimedOut $e) {
                echo 'OperationTimedOut : ' . $e->getMessage();
            } catch (\Exception $e) {
                echo 'Exception : ' . $e->getMessage();
            } finally {

                #dump($ids);
                $this->closeBrowser();
                #return $ids;
            }
        }
    }


    public function handle()
    {

        $busca_ids = $this->argument('busca_ids');
        $acao = $this->option('acao');


        if (method_exists($this, $acao)) {
            #echo "method exists";
            $this->{$acao}($busca_ids);
        } else {
            echo "Este metodo nao existe";
        }

      
        $this->info('Processado com sucesso!');

        return self::SUCCESS;
    }
}
