<?php

namespace App\Console\Commands;

use App\Models\Canal;
use Illuminate\Support\Str;
use App\Models\Video as VideoModel;
use Illuminate\Console\Command;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\ElementNotFoundException;

class Video extends Bot
{

    protected $signature = 'video {video_ids?*} {--acao=craw}';




    public function craw(array $video_ids)
    {

        $queries = VideoModel::whereIn('id', $video_ids)->select('cod', 'id', 'busca_id')->get()->toArray();

        #dd($queries);
        foreach ($queries as $query) {
            $cod = $query['cod'];
            $video_id = $query['id'];
            $busca_id = $query['busca_id'];

            $url = $cod;

            echo "\n" . $url;

            try {

                $this->initBrowser(true);
                $page = $this->browser->createPage();
                $page->navigate($url);

                sleep(5); #precisa sim

                $desc = $nome = $keywords = $slug = $canal_id = $views = $likes = $dislikes = $dt = $caption = $comments = null;

                $metas = $page->dom()->querySelectorAll('meta');
                foreach ($metas as $meta) {
                    if ($meta->getAttribute('name') == 'title') {
                        $nome = $meta->getAttribute('content');
                        $slug = Str::slug($nome);
                    }
                    if ($meta->getAttribute('name') == 'description') {
                        $desc = $meta->getAttribute('content');
                    }
                    if ($meta->getAttribute('name') == 'keywords') {
                        $keywords = $meta->getAttribute('content');
                        $keywords = explode(",",$keywords);
                    }
                }

                #dd($nome,$desc,$keywords,$slug);

                $canal_url = $page->dom()->querySelector('ytd-video-owner-renderer > a.yt-simple-endpoint.style-scope.ytd-video-owner-renderer');
                if ($canal_url) {
                    $cod = $canal_url->getAttribute('href');

                    $parse = 0;
                    $v = compact('busca_id', 'parse');

                    $canal = Canal::updateOrCreate(['cod' => $cod],$v);
                    $canal_id = $canal->id;
                    #dd($res);
                }
                #dd($canal_id);

                $likes = $page->dom()->querySelector('#top-level-buttons-computed > segmented-like-dislike-button-view-model > yt-smartimation > div > div > like-button-view-model > toggle-button-view-model > button-view-model > button > div.yt-spec-button-shape-next__button-text-content');
                if ($likes) {
                    $likes = $likes->getText();
                    $likes = limpaEspacosAcentuacao($likes);
                    $likes = retornaMilMilhaoBilhaoToInt($likes);
                }
                #dump($likes);

                sleep(9); #sem essa porra nao vai **************** nao ta pegando commentarios ....
                $seletor = '#count > yt-formatted-string > span:nth-child(1)';
                $comments = $page->dom()->querySelector($seletor);
                if ($comments) {
                    $comments = $comments->getText();
                    // dd($comments);
                    $comments = limpaEspacosAcentuacao($comments);
                    $comments = filtraDigitos($comments);
                }
                dump('comments=',$comments);


                $seletor = '#count > ytd-video-view-count-renderer > span.view-count.style-scope.ytd-video-view-count-renderer';
                $views = $page->dom()->querySelector($seletor);
                if ($views) {
                    $views = $views->getText();
                    $views = limpaEspacosAcentuacao($views);
                    $views = filtraDigitos($views);
                }



                $page->evaluate("document.querySelector('#expand').click();");
                sleep(2);
                $page->evaluate("document.querySelector('#primary-button > ytd-button-renderer > yt-button-shape > button > yt-touch-feedback-shape > div > div.yt-spec-touch-feedback-shape__fill').click();");
                sleep(4);


                $legendas = $page->dom()->querySelectorAll('#segments-container > ytd-transcript-segment-renderer');
                $re = '^((\d\d?):(\d\d))\s(.+)$';
                $caption = '';
                foreach ($legendas as $legenda) {
                    $legenda = $legenda->getText();
                    $legenda = limpaEspacosAcentuacao($legenda);
                    if (preg_match('/' . $re . '/', $legenda, $res)) {
                        $min = $res[2];
                        $sec = $res[3];
                        $txt = $res[4];
                        $caption .= ' ' . $txt;
                    }
                }
                #dump($caption);

                ########################################### trecho para pegar os dados da API gratis
                $res = $this->apiGratis($url);
                if (is_array($res)) {
                    #extract($res); #id,dateCreated,likes,rawDislikes,rawLikes,dislikes,rating,viewCount,deleted **** id e o problema
                    $dt = datetimeTZtoDateMysql($res['dateCreated']);
                    $views = $res['viewCount'];
                    $likes = $res['likes'];
                    $dislikes = $res['dislikes'];
                    $campos_api_gratis = compact('views','likes','dislikes');
                    #dd($campos_api_gratis);
                }
                ########################################### trecho para pegar os dados da API gratis


                $campos = compact('dt', 'keywords', 'canal_id', 'views', 'likes', 'dislikes', 'desc', 'nome', 'slug', 'caption', 'comments');

                #dump($campos);

                $video = VideoModel::findOrFail($video_id);
                $res = $video->update($campos);
                echo "\n---------- Video numero $video_id atualizado com " . $res ? 'sucesso' : 'erro';


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

        $video_ids = $this->argument('video_ids');
        $acao = $this->option('acao');


        if (method_exists($this, $acao)) {
            #echo "method exists";
            $this->{$acao}($video_ids);
        } else {
            echo "Este metodo nao existe";
        }


        $this->info('Processado com sucesso!');

        #return self::SUCCESS;
    }
}
