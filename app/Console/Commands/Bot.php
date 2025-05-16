<?php

namespace App\Console\Commands;

use DateTime;
use DateTimeZone;
use App\Models\Canal;
use App\Models\Video;
use DateTimeImmutable;
use Illuminate\Support\Str;
use HeadlessChromium\Dom\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\ElementNotFoundException;

class Bot extends Command
{

    protected $signature = 'bot {acao?}';
    protected $browser;

    public function list()
    {

        $url = 'https://www.youtube.com/results?search_query=aborto';

        try {

            $this->initBrowser(true);
            $page = $this->browser->createPage();

            dump('URL: ' . $url);
            $page->navigate($url);

            sleep(5);

            $pags = 3;
            for ($i = 1; $i <= $pags; $i++) {
                sleep(1);
                $page->evaluate("window.scrollTo(0, document.body.scrollHeight || document.documentElement.scrollHeight);");

                $ytds = $page->dom()->querySelectorAll('ytd-video-renderer');
                dump(count($ytds));
            }
            $ids = [];
            $re2 = '\/watch\?v=([a-zA-Z0-9_-]+)';

            foreach ($ytds as $ytd) {
                $html = $ytd->getHtml();
                $html = limpaEspacosTabs($html);

                if (preg_match('/' . $re2 . '/', $html, $res)) {
                    dump($res[1]);
                    $ids[] = Str::wrap($res[1], before: 'https://www.youtube.com/watch?v=', after: '');
                }
            }

            dump($ids);

            $tot = $err = 0;
            foreach ($ids as $cod) {
                dump($cod);
                $parse = 0;
                $busca_id = 3;
                $cod = compact('cod');
                $v = compact('busca_id');
                $res = Video::updateOrCreate(
                    $cod,
                    $v
                );

                $res ? $tot++ : $err++;
            }
            dump("Total upserts: $tot - Erros: $err");
        } catch (OperationTimedOut $e) {
            echo '::::1111' . $e->getMessage();
        } catch (\Exception $e) {
            echo '::::22222 ' . dump($e);
        } finally {

            $this->closeBrowser();
        }
    }


    public function vidiq($id, $youtube_id)
    {

        $url = "https://vidiq.com/youtube-stats/channel/$youtube_id/";

        try {

            $this->initBrowser(true);
            $page = $this->browser->createPage();

            dump('URL: ' . $url);
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

            $categ = urldecode($categ);




            $campos = compact('dt', 'local', 'categ', 'videos', 'inscritos', 'score', 'views', 'min', 'max', 'engagement', 'frequency', 'length');

            #dd($campos);

            $canal = Canal::findOrFail($id);
            $res = $canal->update($campos);
            echo "\n---------- canal numero $id atualizado com " . $res ? 'sucesso' : 'erro';

            dump($canal);
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



    public function canal($id, $cod)
    {

        #$url = 'https://www.youtube.com/watch?v=ZJwL6oLbvPg';


        $url = 'https://www.youtube.com' . urldecode($cod);

        $youtube_id = $dt = $local = $links = $nome = $desc = $slug = $lang = null;

        try {

            $this->initBrowser(false);
            $page = $this->browser->createPage();

            dump('URL: ' . $url);
            $page->navigate($url);

            sleep(6);


            $metas = $page->dom()->querySelectorAll('meta');
            foreach ($metas as $meta) {
                if ($meta->getAttribute('itemprop') == 'identifier') {
                    $youtube_id = $meta->getAttribute('content'); #cuidado, nao e bom usar canal_id p causa das fk do laravel
                }
                if ($meta->getAttribute('itemprop') == 'name') {
                    $nome = $meta->getAttribute('content');
                    $slug = Str::slug($nome);
                }
                if ($meta->getAttribute('itemprop') == 'description') {
                    $desc = $meta->getAttribute('content');
                    $desc = limpaEspacosTabs($desc);
                }
            }
            // <meta itemprop="identifier" content="UCsra3f6ogpXhIZbSUe2OoaA">

            $seletor_mais = '#page-header > yt-page-header-renderer > yt-page-header-view-model > div > div.page-header-view-model-wiz__page-header-headline > div > yt-description-preview-view-model > truncated-text > button > span > span';

            $page->evaluate("document.querySelector('" . $seletor_mais . "').click();");
            sleep(2);




            $res = [
                'videos' => '<td class="style-scope ytd-about-channel-renderer">([\d\.]+?) videos<\/td>',
                'views' => '<td class="style-scope ytd-about-channel-renderer">([\d\.]+) visualizacoes<\/td>',
                'dt' => '>Inscreveu-se em (\d{1,2}) de (.+) de (\d{4})<\/span>',
                'local' => '<td class="style-scope ytd-about-channel-renderer">([\w\s]+)<\/td> <\/tr> <\/tbody>',
                'inscritos' => '<td class="style-scope ytd-about-channel-renderer">([\d,]+)\s(.+) inscritos<\/td> <\/tr>',

            ];

            $re_links = [
                'link' => '>([\d\w\/\.]+)<\/a><\/span><\/div><\/yt-channel-external-link-view-model>',
                'nome_link' => '>([\d\w\s]+)<\/span>',
            ];

            $seletor_modal = '#about-container';
            $modal = $page->dom()->querySelector($seletor_modal);
            if ($modal) {
                $modal = $modal->getHtml();
                $modal = limpaEspacosAcentuacao($modal);


                foreach ($res as $key => $re) {

                    if (preg_match('/' . $re . '/', $modal, $res)) {

                        dump($key, $res);
                        if ($key == 'dt') {
                            $mes = $res[2];
                            $mes = filtraLetras($mes);
                            $mes = retornaMes($mes);
                            $$key = $res[3] . '-' . $mes . '-' . $res[1];
                        } else {
                            $$key = $res[1];
                        }
                    }
                }
            }

            $inscritos = filtraDigitos($inscritos);
            $views = filtraDigitos($views);
            $videos = filtraDigitos($videos);

            $campos = compact('views', 'inscritos', 'videos', 'nome', 'desc', 'slug', 'youtube_id', 'dt', 'local');

            #dd($campos);

            $canal = Canal::findOrFail($id);
            $res = $canal->update($campos);
            echo "\n---------- canal numero $id atualizado com " . $res ? 'sucesso' : 'erro';

            dump($canal);
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



    /* get de video */
    public function getVideo($id, $cod)
    {

        #$url = 'https://www.youtube.com/watch?v=ZJwL6oLbvPg';


        $url = $cod;

        #dd($url);
        #atencao ........... id esta sendo sobrescrito la embaixo
        $video_id = $id;

        $hashtags = $canal_id = $views = $likes = $dislikes = $dt = $desc = $nome = $slug = $caption = $comments = $hashtags = $categ_id = $lang = null;

        try {

            $this->initBrowser(true);
            $page = $this->browser->createPage();

            dump('URL: ' . $url);
            $page->navigate($url);

            sleep(5);

            // $nome = $page->dom()->querySelector('yt-formatted-string.style-scope.ytd-watch-metadata');
            // if ($nome) {
            //     $nome = $nome->getText();
            //     $nome = limpaEspacosAcentuacao($nome);
            //     $slug = Str::slug($nome);
            // }
            # melhor usar as metas

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
                }
            }



            $canal_url = $page->dom()->querySelector('ytd-video-owner-renderer > a.yt-simple-endpoint.style-scope.ytd-video-owner-renderer');
            if ($canal_url) {
                $cod = $canal_url->getAttribute('href');

                $parse = 0;
                $busca_id = 3;
                $v = compact('busca_id', 'parse');

                $canal = Canal::updateOrCreate(
                    ['cod' => $cod],
                    $v
                );
                $canal_id = $canal->id;
                #dd($res);
            }
            #dump($canal_url);

            $likes = $page->dom()->querySelector('#top-level-buttons-computed > segmented-like-dislike-button-view-model > yt-smartimation > div > div > like-button-view-model > toggle-button-view-model > button-view-model > button > div.yt-spec-button-shape-next__button-text-content');
            if ($likes) {
                $likes = $likes->getText();
                $likes = limpaEspacosAcentuacao($likes);
            }
            dump($likes);


            $re = '#([^\s]+)';
            $desc = $page->dom()->querySelector('#description-inner');
            if ($desc) {
                $desc = $desc->getText();
                $desc = limpaEspacosAcentuacao($desc);
                if (preg_match_all('/' . $re . '/', $desc, $res)) {
                    #dump($res);
                    $hashtags = array_unique($res[1]);
                }
            }
            dump($desc);

            sleep(8); #sem essa porra nao vai
            $seletor = '#count > yt-formatted-string > span:nth-child(1)';
            $comments = $page->dom()->querySelector($seletor);
            if ($comments) {
                $comments = $comments->getText();
                $comments = limpaEspacosAcentuacao($comments);
                $comments = filtraDigitos($comments);
            }
            #dd($comments);


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
            dump($caption);


            $res = $this->apiGratis($url);
            if (is_array($res)) {
                #extract($res); #id,dateCreated,likes,rawDislikes,rawLikes,dislikes,rating,viewCount,deleted **** id e o problema
                $dt = datetimeTZtoDateMysql($res['dateCreated']);
                $views = $res['viewCount'];
                $likes = $res['likes'];
                $dislikes = $res['dislikes'];
            }


            $campos = compact('dt', 'hashtags', 'canal_id', 'views', 'likes', 'dislikes', 'desc', 'nome', 'slug', 'caption', 'comments', 'hashtags', 'categ_id', 'lang');

            $video = Video::findOrFail($video_id);
            $res = $video->update($campos);
            echo "\n---------- Video numero $id atualizado com " . $res ? 'sucesso' : 'erro';

            dump($video);
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



    function crawl($url, $httpHeaders = [], $prompt = '', $verb = 'GET')
    {

        $curl = curl_init();

        if (!empty($prompt) && $verb == 'POST') {
            $post_fields = [
                "model" => "gpt-3.5-turbo",
                "messages" => [
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ],
            ];
            $postFields = json_encode($post_fields);
        } else {
            $postFields = null;
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $verb,
            CURLOPT_POSTFIELDS => $postFields,

            CURLOPT_HTTPHEADER => $httpHeaders,

            CURLOPT_HEADER         => false,            // don't return headers
            CURLOPT_FOLLOWLOCATION => true,             // follow redirects
            CURLOPT_ENCODING       => '',               // handle all encodings
            CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)',      // who am i
            CURLOPT_AUTOREFERER    => true,             // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 30,              // timeout on connect
            CURLOPT_TIMEOUT        => 30,              // timeout on response
            CURLOPT_MAXREDIRS      => 5,                // stop after 5 redirects

        ]);

        $res = curl_exec($curl);
        #dump($res);

        if (!curl_errno($curl)) {
            switch ($httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
                case 200:  # OK
                    break;
                default:
                    echo 'Unexpected HTTP code: ', $httpcode, "\n";
            }
        }
        curl_close($curl);
        return ($httpcode >= 200 && $httpcode < 300) ? $res : false;
    }


    function getChatGptFromText($prompt)
    {

        $url = 'https://api.openai.com/v1/chat/completions';
        $key = env('OPENAI_API_KEY');
        $httpHeaders = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key,
        ];

        $res = $this->crawl($url, $httpHeaders, $prompt, 'POST');

        #dump($res);
        $json = json_decode($res, true);
        #$completion = $json->choices[0]->message->content;
        if (isset($json['choices'][0]['message']['content'])) {
            $completion = $json['choices'][0]['message']['content'];
            $res = json_decode($completion, true);
            #dd($resp);
            return $res;
        } else {
            return false;
        }
    }



    function apiGratis($url)
    {

        $ped = preg_replace('/https:\/\/www\.youtube\.com\/watch\?v=/', '', $url);


        $url = 'https://returnyoutubedislikeapi.com/votes?videoId=' . $ped;
        $res = $this->crawl($url);

        $arr = json_decode($res, true);
        #dd($arr);
        if (is_array($arr)) {

            return $arr;
        } else {
            return false;
        }
    }



    function initBrowser($headless = true)
    {
        $browserFactory = new BrowserFactory();

        $browser = $browserFactory->createBrowser([
            'headless' => $headless,
            'windowSize'   => [1920, 1080],
            'noSandbox' => true,
            'customFlags' => ['--lang=pt-BR'],

            #'customFlags' => ['--proxy-server=http://104.207.54.209:3128'],
        ]);
        $this->browser = $browser;
    }

    function closeBrowser()
    {
        $this->browser->close();
    }

    public function handle()
    {

        $acao = $this->argument('acao');
        #$this->list();




        $canals = Canal::where('parse', '=', 0)->get()->select('id', 'cod', 'youtube_id')->toArray();
        foreach ($canals as $canal) {
            extract($canal); #id cod
            if (!$cod)
                continue;
            #$this->canal($id, $cod);
            $this->vidiq($id, $youtube_id);
        }

        dd('---------------------------------------------');

        $videos = Video::where('parse', '=', 0)->get()->select('id', 'cod')->toArray();

        foreach ($videos as $video) {
            extract($video); #id cod
            if (!$cod)
                continue;
            $this->get($id, $cod);
        }
    }
}
