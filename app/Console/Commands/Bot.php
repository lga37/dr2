<?php

namespace App\Console\Commands;

use DateTime;
use DateTimeZone;
use App\Models\Canal;
use App\Models\Video;
use DOM\HtmlDocument;
use DateTimeImmutable;
use Illuminate\Support\Str;
use HeadlessChromium\Dom\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use HeadlessChromium\BrowserFactory;
use Illuminate\Support\Facades\Http;
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






    function getInscritos444444444()
    {
        $youtube_id = "UCQi67q4kGdmnJaRzX81uK5g";

        $url_base = "youtube.com/channel/$youtube_id";

        $variacoes = ['http://', 'https://', '', 'www.', 'http://www.', 'https://www.'];

        foreach ($variacoes as $v) {
            $url2 = $v . $url_base;

            $wayback = "https://archive.org/wayback/available?url=$url2";
            #$res = Http::timeout(10)->get($wayback)->json();

            $res = Http::timeout(12)
                ->retry(3, 5000)
                ->get($wayback)
                ->json();

            if (!($res['archived_snapshots']['closest']['available'] ?? false)) {
                continue;
            }

            $cdx_url = "https://web.archive.org/cdx/search/cdx?url=$url2";
            $txt = Http::timeout(13)->retry(3, 5000)->get($cdx_url)->body();

            if (!preg_match_all('/\s(\d{12,})\s(.+?)\s/', $txt, $match)) {
                echo "❌ Não encontrou timestamps para $url2\n";
                continue;
            }

            foreach ($match[1] as $ts) {
                #$url_final = "http://web.archive.org/web/$ts/http://www.youtube.com/channel/$youtube_id";
                $url_final = "http://web.archive.org/web/$ts/http://www.youtube.com/channel/$url2";


                try {
                    $this->initBrowser(true);
                    $page = $this->browser->createPage();
                    $page->navigate($url_final);

                    sleep(20);

                    #ru-RU es # se cair aqui ele da um continue
                    if ($elem = $page->dom()->querySelector("html")) {
                        if ($lang = $elem->getAttribute('lang')) {
                            echo "\n-------idioma: $lang --- \n";
                            if ($lang) {
                                $lang = substr($lang, 0, 2);
                                if (in_array($lang, ['ru'])) {
                                    echo "\n--------- idioma ----------- Site em $lang \n\n";
                                    continue;
                                }
                            }
                        }
                    }

                    $seletor1 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.yt-uix-tooltip';
                    $seletor2 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.subscribed.yt-uix-tooltip'; #aria-label
                    $seletor3 = 'span.yt-core-attributed-string.yt-content-metadata-view-model-wiz__metadata-text yt-core-attributed-string--white-space-pre-wrap.yt-core-attributed-string--link-inherit-color';
                    $seletor4 = 'span.yt-core-attributed-string.yt-content-metadata-view-model-wiz__metadata-text yt-core-attributed-string--white-space-pre-wrap.yt-core-attributed-string--link-inherit-color > span';
                    $seletor5 = '#subscriber-count.style-scope.ytd-c4-tabbed-header-renderer'; #esse aqui e pelo aria-label
                    $seletor6 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.subscribed'; #so com gettext

                    if ($elem = $page->dom()->querySelector($seletor1)) {
                        #echo $elem->getHTML();
                        $subscribers = $elem->getAttribute('title');
                        echo "subs::" . $subscribers;
                        $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                        echo "\n if1 $subscribers \n";
                        if ($subscribers > 0) {
                            $parsed = 1;
                            $r = ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                            dump($r);
                        }
                    } elseif ($elem = $page->dom()->querySelector($seletor2)) {
                        #echo $elem->getHTML();
                        $subscribers = $elem->getAttribute('title');
                        echo "subs::" . $subscribers;
                        $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                        echo "\n if2 $subscribers \n";

                        if ($subscribers > 0) {
                            $parsed = 1;
                            ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                        }
                    } elseif ($elem = $page->dom()->querySelector($seletor3)) {
                        #echo $elem->getHTML();
                        $subscribers = $elem->getText();
                        echo "subs::" . $subscribers;
                        $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                        echo "\n if3 $subscribers \n";

                        if ($subscribers > 0) {
                            $parsed = 1;
                            ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                        }
                    } elseif ($elem = $page->dom()->querySelector($seletor4)) {
                        #echo $elem->getHTML();
                        $subscribers = $elem->getText();
                        echo "subs::" . $subscribers;
                        $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                        echo "\n if4 $subscribers \n";
                        if ($subscribers > 0) {
                            $parsed = 1;
                            ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                        }
                    } elseif ($elem = $page->dom()->querySelector($seletor5)) {
                        $subscribers = $elem->getAttribute('aria-label');
                        $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                        echo "\n if5 $subscribers \n";

                        if ($subscribers > 0) {
                            $parsed = 1;
                            ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                        }
                    } elseif ($elem = $page->dom()->querySelector($seletor6)) {
                        $subscribers = $elem->getText();
                        $subscribers = retornaMilMilhaoBilhaoToInt($subscribers);
                        echo "\n if6 $subscribers \n";

                        if ($subscribers > 0) {
                            $parsed = 1;
                            ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                        }
                    } else {

                        #faz via regex no wget
                        $re = '"subscriberCountText":{"runs":\[{"text":"(.+?) subscribers"}';

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url_final);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $txt = curl_exec($ch);
                        curl_close($ch);

                        $res = [
                            '"subscriberCountText":{"runs":\[{"text":"(.+?) subscribers"}',
                            '{"text":{"content":"([\d\.KkmM]+?) subscribers"}}',
                        ];

                        foreach ($res as $key => $re) {
                            if (preg_match('/' . $re . '/', $txt, $res)) {
                                #dd($res[1]);
                                $subscribers = $res[1];
                                $subscribers = retornaMilMilhaoBilhaoToInt($subscribers); #19.5K subscribers
                                #echo $txt;
                                echo "\n\n regex $subscribers chave $key \n";
                                if ($subscribers > 0) {
                                    $parsed = 1;
                                    ArxivModel::where('id', $arxiv_id)->update(compact('subscribers', 'parsed'));
                                }
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    echo 'Exception : ' . $e->getMessage();
                } finally {
                    $this->closeBrowser();
                }
            }
        }

        echo "⚠️ Nenhum dado confiável encontrado para $youtube_id\n";
        return null;
    }



    function getInscritos555555(): array
    {

        $youtube_id = "UCQi67q4kGdmnJaRzX81uK5g";

        #$this->initBrowser(true);

        $result = [];
        $url_base = "youtube.com/channel/$youtube_id";
        $variacoes = ['http://', 'https://', '', 'www.', 'http://www.', 'https://www.'];

        foreach ($variacoes as $v) {
            echo "\n\nINICIANDO $v\n\n";
            $url2 = $v . $url_base;

            $wayback = "https://archive.org/wayback/available?url=$url2";
            $res = Http::timeout(12)->retry(3, 5000)->get($wayback)->json();
            if (!($res['archived_snapshots']['closest']['available'] ?? false)) continue;

            $cdx_url = "https://web.archive.org/cdx/search/cdx?url=$url2";
            $txt = Http::timeout(13)->retry(3, 5000)->get($cdx_url)->body();
            if (!preg_match_all('/\s(\d{12,})\s(.+?)\s/', $txt, $match)) continue;

            dump($match[1]); #ate aqui ta certo mas ta vindo muitos regs 
            foreach ($match[1] as $ts) {
                $url_final = "http://web.archive.org/web/$ts/$url2";

                echo "\n\n$url_final\n\n";


                try {
                    $html = Http::timeout(13)->retry(3, 5000)->get($url_final)->body();

                    # 1. Verificar idioma proibido
                    $idiomas_proibidos = ['ru',];
                    if (preg_match('/<html[^>]*lang="([a-z]{2})"/i', $html, $matchLang)) {
                        $lang = strtolower($matchLang[1]);
                        echo "\n🈷️ Idioma detectado: $lang";

                        if (in_array($lang, $idiomas_proibidos)) {
                            echo "\n⚠️ Ignorando página com idioma proibido: $lang\n";
                            continue;
                        }
                    }

                    # 2. Regex para inscritos
                    $subs = null;
                    $regexSubs = [
                        '/<span[^>]*class="[^"]*yt-subscription-button-subscriber-count-branded-horizontal[^"]*subscribed[^"]*"[^>]*>([\d\.,]+)<\/span>/i',
                        '/"subscriberCountText":\{"simpleText":"([\d\.,KMkm]+) subscribers"\}/', // fallback JSON
                    ];

                    foreach ($regexSubs as $re) {
                        if (preg_match($re, $html, $m)) {
                            $subsRaw = $m[1];
                            echo "\n👀 Encontrado via regex: $subsRaw";
                            $subs = retornaMilMilhaoBilhaoToInt($subsRaw);
                            break;
                        }
                    }

                    # 3. Se encontrou, adiciona ao resultado
                    if ($subs > 0) {
                        echo "\n✅ Inscritos: $subs para timestamp $ts";
                        $result[$ts] = $subs;
                    }
                } catch (\Exception $e) {
                    echo "❌ Erro: " . $e->getMessage();
                }
            }
        }

        #$this->closeBrowser();
        dd($result);
        return $result;
    }




    protected function getWaybackSamples(string $youtubeId, int $sampleSize = 10): array
    {


        #$youtubeId = 'UCQi67q4kGdmnJaRzX81uK5g';

        // // Use wildcard + matchType pra evitar iterar http/https/www
        // $target = "*.youtube.com/channel/$youtubeId";

        // $youtubeId = 'UCQi67q4kGdmnJaRzX81uK5g';


        $params = [
            // use o host completo; o CDX casa melhor que "youtube.com"
            'url'       => "https://www.youtube.com/channel/{$youtubeId}",
            'matchType' => 'exact',
            'output'    => 'json',
            'fl'        => 'timestamp,original,statuscode,mimetype,digest',
            'from'      => '20140101',
            'to'        => '20250606',

            // mantenha só o filtro negativo pra robots; remova statuscode/mimetype
            'filter'    => ['!original:*robots.txt*'],

            // reduza densidade temporal e dedupe conteúdo
            'collapse'  => ['timestamp:6', 'digest'],

            // pegue bastante e, se ainda vier muito, você amostra depois no PHP
            'limit'     => 50000,
        ];

        // monte a URL
        $cdx = "https://web.archive.org/cdx/search/cdx?" . http_build_query($params);


        echo "\n\n\n$cdx\n\n\n";



        $resp = Http::timeout(12)->retry(3, 300)->get($cdx);
        $list = $resp->json() ?? [];
        $rows = array_slice($list, 1); // primeira linha é o header

        $pairs = array_map(fn($r) => [
            'ts'  => $r[0],     // timestamp (porque fl=timestamp,original,statuscode,mimetype,digest)
            'url' => "http://web.archive.org/web/{$r[0]}/{$r[1]}",
        ], $rows);

        // dedup (timestamp|url) só por garantia
        $pairs = collect($pairs)->unique(fn($p) => $p['ts'] . '|' . $p['url'])->values()->all();

        // embaralha e pega amostra
        shuffle($pairs);
        $sample = array_slice($pairs, 0, $sampleSize);

        return $sample;


        ######################################################################################
        ######################################################################################
        // $urlBase   = "youtube.com/channel/$youtubeId";
        // $variacoes = ['http://', 'https://', '', 'www.', 'http://www.', 'https://www.'];

        // $pairs = [];

        // foreach ($variacoes as $v) {
        //     $target = $v . $urlBase;

        //     // CDX com filtros e colapso pra reduzir volume
        //     $cdx = "https://web.archive.org/cdx/search/cdx?" . http_build_query([
        //         'url'    => $target,
        //         'output' => 'json',
        //         'filter' => 'statuscode:200',
        //         // 6 -> colapsa por ano-mês (YYYYMM). Use 8 se quiser 1/dia.
        //         'collapse' => 'timestamp:6',
        //         'limit'  => 20000, // só pra garantir
        //     ]);

        //     $resp = Http::timeout(12)->retry(3, 300)->get($cdx);
        //     if (!$resp->ok()) continue;

        //     $json = $resp->json();
        //     if (!is_array($json) || count($json) <= 1) continue;

        //     // primeira linha é header
        //     foreach (array_slice($json, 1) as $row) {
        //         // CDX columns: urlkey, timestamp, original, mimetype, statuscode, digest, length
        //         $ts  = $row[1] ?? null;
        //         if (!$ts) continue;

        //         $pairs[] = [
        //             'ts'  => $ts,
        //             'url' => "http://web.archive.org/web/{$ts}/{$target}",
        //         ];
        //     }
        // }

        // // Dedup por timestamp+url
        // $pairs = collect($pairs)
        //     ->unique(fn($p) => $p['ts'] . '|' . $p['url'])
        //     ->values()
        //     ->all();

        // // Embaralha e pega amostra
        // shuffle($pairs);
        // return array_slice($pairs, 0, $sampleSize);
    }

    protected function scrapeSubscribersFromSamples(array $pairs): array
    {
        $result = [];

        foreach ($pairs as $p) {
            $ts  = $p['ts'];
            $url = $p['url'];

            try {
                $res = Http::timeout(13)->retry(3, 500)->get($url);
                if (!$res->ok())
                    continue; // evita 404 da Wayback
                $html = $res->body();

                // (opcional) pular idiomas proibidos
                if (preg_match('/<html[^>]*lang="([a-z]{2})"/i', $html, $m)) {
                    $lang = strtolower($m[1]);
                    if (in_array($lang, ['ru'])) continue;
                }

                // regex/JSON fallback
                $subs = null;
                $regexSubs = [
                    '/<span[^>]*class="[^"]*yt-subscription-button-subscriber-count-branded-horizontal[^"]*subscribed[^"]*"[^>]*>([\d\.,]+)<\/span>/i',
                    '/"subscriberCountText":\{"simpleText":"([\d\.,KMkm]+) subscribers"\}/',
                ];

                foreach ($regexSubs as $re) {
                    if (preg_match($re, $html, $m)) {
                        $subs = retornaMilMilhaoBilhaoToInt($m[1]);
                        break;
                    }
                }

                if ($subs && $subs > 0) {
                    $result[$ts] = $subs;
                }
            } catch (\Throwable $e) {
                echo 'Wayback scrape erro' . $url . ' e: ' . $e->getMessage();
            }
        }

        ksort($result); // ordena por tempo
        return $result;
    }

    public function getInscritos(): array
    {
        $youtube_id = "UCQi67q4kGdmnJaRzX81uK5g";
        $pairs  = $this->getWaybackSamples($youtube_id, 10); // amostra de 10
        dump($pairs);
        $result = $this->scrapeSubscribersFromSamples($pairs);
        dump($result);
        // dd($pairs, $result); // pra inspecionar
        return $result;
    }




    public function handle()
    {

        $acao = $this->argument('acao');
        #$this->list();

        $this->getInscritos();


        // $canals = Canal::where('parse', '=', 0)->get()->select('id', 'cod', 'youtube_id')->toArray();
        // foreach ($canals as $canal) {
        //     extract($canal); #id cod
        //     if (!$cod)
        //         continue;
        //     #$this->canal($id, $cod);
        //     $this->vidiq($id, $youtube_id);
        // }

        // dd('---------------------------------------------');

        // $videos = Video::where('parse', '=', 0)->get()->select('id', 'cod')->toArray();

        // foreach ($videos as $video) {
        //     extract($video); #id cod
        //     if (!$cod)
        //         continue;
        //     $this->get($id, $cod);
        // }
    }
}
