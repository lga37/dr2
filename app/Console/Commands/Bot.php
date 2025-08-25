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




    function patternsFromChannelId(string $channelId): array
    {

        $apiKey = env('YOUTUBE_API_KEY');
        $patterns = [];
        $patterns[] = "https://www.youtube.com/channel/$channelId/*";

        // --- 1) Tenta via API: snippet.customUrl ---
        $apiUrl = 'https://www.googleapis.com/youtube/v3/channels?part=snippet&id=' . urlencode($channelId) . '&key=' . urlencode($apiKey);
        $res = @json_decode(@file_get_contents($apiUrl), true);
        $customUrl = $res['items'][0]['snippet']['customUrl'] ?? null;

        if ($customUrl) {
            if ($customUrl[0] === '@') {
                // handle atual
                $handle = ltrim($customUrl, '@');
                $patterns[] = "https://www.youtube.com/@$handle/*";
            } else {
                // custom antigo
                $patterns[] = "https://www.youtube.com/c/$customUrl/*";
                $patterns[] = "https://www.youtube.com/$customUrl/*";
            }
        }

        // --- 2) Tenta via redirect do /channel/UC... para descobrir rota “viva” ---
        $finalUrl = $this->resolveFinalYoutubeUrl("https://www.youtube.com/channel/$channelId");
        if ($finalUrl) {
            // Extrai caminho base
            $path = parse_url($finalUrl, PHP_URL_PATH) ?? '';
            // Normaliza e adiciona os possíveis padrões
            // Exemplos de $path: /@handle , /user/xyz , /c/Custom , /Custom
            if (preg_match('#^/@([A-Za-z0-9._-]+)$#', $path, $m)) {
                $patterns[] = "https://www.youtube.com/@{$m[1]}/*";
            } elseif (preg_match('#^/user/([A-Za-z0-9._-]+)$#', $path, $m)) {
                $patterns[] = "https://www.youtube.com/user/{$m[1]}/*";
            } elseif (preg_match('#^/c/([A-Za-z0-9._-]+)$#', $path, $m)) {
                $patterns[] = "https://www.youtube.com/c/{$m[1]}/*";
                $patterns[] = "https://www.youtube.com/{$m[1]}/*";
            } elseif (preg_match('#^/([A-Za-z0-9._-]+)$#', $path, $m)) {
                // “seco” (direto na raiz)
                $patterns[] = "https://www.youtube.com/{$m[1]}/*";
            }
        }

        // Únicos e ordenados
        $patterns = array_values(array_unique(array_filter($patterns)));
        return $patterns;
    }

    function resolveFinalYoutubeUrl(string $url, int $timeout = 10): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY        => true,  // HEAD (rápido); mude p/ false se precisar do HTML
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
        ]);
        curl_exec($ch);
        $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($effective && $httpCode >= 200 && $httpCode < 400) {
            return $effective;
        }
        return null;
    }

    function cdxList(string $pattern): array
    {
        $base = 'https://web.archive.org/cdx/search/cdx';

        // monta params básicos
        $params = [
            'url'    => $pattern,                        // ex.: https://www.youtube.com/@opovo/*  (use /*!)
            'output' => 'json',
            'fields' => 'timestamp,original,statuscode,mimetype',
            'limit'  => '100000',
        ];

        // query base
        $q = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        // filtros (por padrão, mantém só 200)
        $filters = array_merge(['statuscode:200'], ['mimetype:text/html']); // ex.: ['mimetype:text/html']
        foreach ($filters as $f) {
            $q .= '&filter=' . rawurlencode($f);
        }

        // chamada
        $json = @file_get_contents("$base?$q");
        $rows = json_decode($json, true) ?: [];

        // Remover header se vier (pode ser ['timestamp','original',...] ou ['urlkey','timestamp',...])
        if ($rows && is_array($rows[0]) && (in_array('timestamp', $rows[0], true) || in_array('urlkey', $rows[0], true))) {
            array_shift($rows);
        }

        if (!$rows) return [];



        // Caso 7 colunas (padrão: urlkey,timestamp,original,mimetype,statuscode,digest,length)
        if (isset($rows[0]) && count($rows[0]) === 7) {
            return array_map(function ($r) {
                return [
                    'ts'        => $r[1],           // YYYYMMDDhhmmss
                    'original'  => $r[2],           // URL
                    'status'    => (int)$r[4],      // statuscode
                    'mimetype'  => $r[3],           // mimetype
                ];
            }, $rows);
        }

        // Caso 4 colunas (timestamp,original,statuscode,mimetype)
        return array_map(function ($r) {
            return [
                'ts'        => $r[0],
                'original'  => $r[1],
                'status'    => (int)$r[2],
                'mimetype'  => $r[3],
            ];
        }, $rows);
    }


    function mergeTimestamps(array $patterns): array
    {
        $seen = [];
        foreach ($patterns as $p) {
            foreach ($this->cdxList($p) as $row) {
                $seen[$row['ts']] = $row; // dedupe por timestamp
            }
        }
        ksort($seen); // ordem cronológica
        return array_values($seen);
    }


    // 4) Amostra ~k pontos regularmente espaçados no tempo (quantis temporais)
    function sampleEvenlyByTime(array $captures, int $k = 10): array
    {
        $n = count($captures);
        if ($n <= $k) return $captures;

        // mapeia ts -> unix
        $pairs = [];
        foreach ($captures as $row) {
            $ts = $row['ts'];
            $dt = DateTimeImmutable::createFromFormat('YmdHis', $ts, new DateTimeZone('UTC'));
            if ($dt === false) continue;
            $pairs[] = ['unix' => $dt->getTimestamp(), 'row' => $row];
        }
        if (!$pairs) return [];

        // já devem vir ordenados, mas garantimos
        usort($pairs, fn($a, $b) => $a['unix'] <=> $b['unix']);

        $min = $pairs[0]['unix'];
        $max = $pairs[count($pairs) - 1]['unix'];
        if ($max <= $min) {
            // tudo no mesmo instante — pega head, alguns do meio e tail por índice
            $step = ($n - 1) / max(1, $k - 1);
            $out = [];
            for ($i = 0; $i < $k; $i++) {
                $idx = (int) round($i * $step);
                $out[] = $captures[$idx];
            }
            return $out;
        }

        // targets por quantil no tempo
        $out = [];
        $j = 0; // ponteiro no array de pairs
        for ($i = 0; $i < $k; $i++) {
            $target = $min + ($i * ($max - $min) / max(1, $k - 1));
            while ($j < count($pairs) - 1 && $pairs[$j]['unix'] < $target) {
                $j++;
            }
            $out[] = $pairs[$j]['row'];
        }

        // dedupe caso pegue o mesmo vizinho em alvos próximos
        $seen = [];
        $uniq = [];
        foreach ($out as $r) {
            $key = $r['ts'] . '|' . $r['original'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniq[] = $r;
            }
        }

        // se por acaso ficou com menos que k (por dedupe), preenche por step de índice
        if (count($uniq) < $k) {
            $step = ($n - 1) / max(1, $k - 1);
            for ($i = 0; $i < $k && count($uniq) < $k; $i++) {
                $idx = (int) round($i * $step);
                $cand = $captures[$idx];
                $key  = $cand['ts'] . '|' . $cand['original'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniq[] = $cand;
                }
            }
            // reordena pela data
            usort($uniq, fn($a, $b) => strcmp($a['ts'], $b['ts']));
        }

        return $uniq;
    }



    // Usa seu headless atual para retornar o HTML da página
    function http_get(string $url, int $timeoutMs = 15000, int $extraSleepMs = 0): ?string
    {
        try {
            // você já tem esses helpers
            $this->initBrowser(true);
            $page = $this->browser->createPage();

            // navega e espera carregamento
            $page->navigate($url);
            try {
                // espere LOAD; se sua lib tiver NETWORK_IDLE, melhor ainda
                $page->waitForNavigation(\HeadlessChromium\Page::LOAD, $timeoutMs);
            } catch (\Throwable $e) {
                // fallback bruto
                usleep(min(20000, $timeoutMs) * 1000);
            }

            if ($extraSleepMs > 0) usleep($extraSleepMs * 1000);

            // pega HTML “do DOM” (mais confiável que getHtml())
            $eval   = $page->evaluate('document.documentElement.outerHTML');
            $result = $eval->getReturnValue();

            return is_string($result) && $result !== '' ? $result : null;
        } catch (\Throwable $e) {
            // log se quiser
            return null;
        } finally {
            // importante: feche a page; se quiser reusar o browser, não mate o browser aqui
            try {
                isset($page) && $page->close();
            } catch (\Throwable $e) {
            }
            $this->closeBrowser();
        }
    }






    // ---- Wayback fetch (tenta id_/plain) ----
    function wayback_fetch_html(string $ts, string $original, ?string &$snapshotUrl = null): ?string
    {
        $candidates = [
            #"https://web.archive.org/web/{$ts}id_/{$original}",
            "https://web.archive.org/web/{$ts}/{$original}",
        ];
        foreach ($candidates as $u) {
            if ($html = $this->http_get($u)) {
                // sanity check: precisa parecer HTML
                if (stripos($html, '<html') !== false || stripos($html, 'ytInitial') !== false) {
                    $snapshotUrl = $u;   // <-- AQUI guardamos a URL COMPLETA do Wayback
                    return $html;
                }
            }
        }
        $snapshotUrl = null;
        return null;
    }



    function subs_to_int(string $txt): ?int
    {
        $s = trim(mb_strtolower($txt, 'UTF-8'));
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace('/\x{00A0}+/u', ' ', $s); // nbsp

        // PT e variações sem acento
        $s = str_replace(
            [' mil ', ' mil', 'mi', 'milhão', 'milhoes', 'milhões', 'bilhao', 'bilhão', 'bilhoes', 'bilhões'],
            [' k ',   ' k',   'm', 'm',      'm',       'm',        'b',      'b',      'b',       'b'],
            $s
        );
        // EN
        $s = str_replace(
            [' thousand ', ' thousand', 'million', 'millions', 'billion', 'billions'],
            [' k ',        ' k',        'm',       'm',        'b',       'b'],
            $s
        );

        // número + sufixo opcional k/m/b, perto de "subscribers/inscritos"
        if (preg_match('/([\d\.\,]+)\s*([kmb])?(?=[^\w]|\b)(?=.*\b(subscribers|inscritos)\b)/iu', $s, $m)) {
            $num = $m[1];
            $suf = isset($m[2]) ? strtolower($m[2]) : '';

            if ($suf) {
                // normaliza decimal: 1.234,5 -> 1234.5 ; 1,2 -> 1.2
                if (strpos($num, ',') !== false && strpos($num, '.') !== false) {
                    $num = str_replace('.', '', $num);
                    $num = str_replace(',', '.', $num);
                } elseif (strpos($num, ',') !== false) {
                    $num = str_replace(',', '.', $num);
                }
                $val  = (float)$num;
                $mult = ['k' => 1_000, 'm' => 1_000_000, 'b' => 1_000_000_000][$suf] ?? 1;
                return (int) round($val * $mult);
            } else {
                $digits = preg_replace('/\D+/', '', $num);
                if ($digits !== '' && ctype_digit($digits)) return (int)$digits;
            }
        }
        return null;
    }

    function extract_subscribers(string $html): ?int
    {
        // --- 0) normalizações rápidas para facilitar matching ---
        // decodifica entidades (&nbsp; etc.) uma vez
        $htmlDec = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 1) JSON: "subscriberCountText":{"simpleText":"1.23M subscribers"}
        if (preg_match('/"subscriberCountText"\s*:\s*\{\s*"simpleText"\s*:\s*"([^"]+)"/i', $htmlDec, $m)) {
            if ($n = $this->subs_to_int($m[1])) {
                echo "\n--1 (simpleText): $n\n";
                return $n;
            }
        }

        // 2) JSON: "subscriberCountText":{"runs":[{"text":"1.23M"},{"text":" subscribers"}]}
        if (preg_match('/"subscriberCountText"\s*:\s*\{\s*"runs"\s*:\s*\[(.*?)\]\s*\}/is', $htmlDec, $m)) {
            preg_match_all('/"text"\s*:\s*"([^"]+)"/i', $m[1], $mm);
            $joined = implode(' ', $mm[1] ?? []);
            if ($n = $this->subs_to_int($joined)) {
                echo "\n--2 (runs): $n\n";
                return $n;
            }
        }

        // ------------------------------------------------------------------
        // 3) BANCO DE REGEX (fácil de editar)
        // Cada regex deve capturar em ( ... ) o TEXTO que contém "subscribers|inscritos"
        // Você pode adicionar/remover itens aqui à vontade.
        // ------------------------------------------------------------------
        $regexBank = [

            // 3.1) yt-formatted-string com aria-label
            // <yt-formatted-string id="subscriber-count" ... aria-label="433K subscribers">...</yt-formatted-string>
            'yt_formatted_aria' =>
            '/<yt-formatted-string[^>]+id="subscriber-count"[^>]*aria-label="([^"]*?(?:subscribers|inscritos)[^"]*)"/iu',

            // 3.2) yt-formatted-string com texto interno
            // <yt-formatted-string id="subscriber-count">279K subscribers</yt-formatted-string>
            'yt_formatted_inner' =>
            '/<yt-formatted-string[^>]+id="subscriber-count"[^>]*>(.*?)<\/yt-formatted-string>/isu',

            // 3.3) bloco WIZ (span do core contendo "inscritos/subscribers")
            // <span class="yt-core-attributed-string ...">1,36 mi de inscritos</span>
            'wiz_core_span' =>
            '/<span[^>]*class="[^"]*\byt-core-attributed-string\b[^"]*"[^>]*>\s*([^<]*?(?:subscribers|inscritos)[^<]*)\s*<\/span>/iu',

            // 3.4) bloco WIZ mais focado (procura o container e, dentro, o span)
            'wiz_row_span' =>
            '/<div[^>]*class="[^"]*\byt-content-metadata-view-model-wiz__metadata-row\b[^"]*"[^>]*>.*?' .
                '<span[^>]*class="[^"]*\byt-core-attributed-string\b[^"]*"[^>]*>\s*([^<]*?(?:subscribers|inscritos)[^<]*)\s*<\/span>/isu',

            // 3.5) marcações antigas com "subscriber-count" em classes genéricas
            'legacy_class' =>
            '/class="[^"]*subscriber[^"]*count[^"]*"[^>]*>([^<]*?(?:subscribers|inscritos)[^<]*)</iu',

            // 3.6) JSON "metadataParts" no HTML (text.content)
            // {"metadataParts":[{"text":{"content":"1.32M subscribers"},"accessibilityLabel":"1.32 million subscribers"}, ...]}
            'metadata_parts_text' =>
            '/"text"\s*:\s*\{\s*"content"\s*:\s*"([^"]*?(?:subscribers|inscritos)[^"]*)"/iu',

            // 3.7) JSON "metadataParts" no HTML (accessibilityLabel)
            'metadata_parts_a11y' =>
            '/"accessibilityLabel"\s*:\s*"([^"]*?(?:subscribers|inscritos)[^"]*)"/iu',

            // 3.8) fallback genérico: pega uma janela com número + sufixo perto de "subscribers/inscritos"
            'generic_nearby' =>
            '/.{0,120}(?:\b[\d\.,]+(?:\s*[kmb]|\s*(?:mil(?:hão|hoes|hões)?|million|billion|thousand))\b[^<>\n\r]*?(?:subscribers|inscritos)).{0,40}/iu',
        ];

        foreach ($regexBank as $label => $re) {
            if (preg_match_all($re, $htmlDec, $matches)) {
                // Alguns regex capturam múltiplos trechos; tente todos até achar um parseável
                foreach ($matches[1] as $raw) {
                    // Para casos com tags internas, remova-as
                    $txt = trim(strip_tags($raw));
                    if ($txt === '') continue;

                    if ($n = $this->subs_to_int($txt)) {
                        echo "\n--RE[$label]: $n | '$txt'\n";
                        return $n;
                    }
                }
            }
        }

        return null;
    }
 

    // ---- Loop nos samples → [{ts, original, archive, subs}] ----
    function scrape_samples_subscribers(array $samples, int $sleepMs = 0): array
    {
        $out = [];
        foreach ($samples as $row) {
            $ts  = $row['ts'];
            $url = $row['original'];

            $archiveUrl = null;
            $html = $this->wayback_fetch_html($ts, $url, $archiveUrl);
            $subs = $html ? $this->extract_subscribers($html) : null;

            $out[] = [
                'ts'       => $ts,
                'original' => $url,
                'archive'  => $archiveUrl,  // <-- URL completa do snapshot usada
                'subs'     => $subs,        // null se não achar
            ];

            if ($sleepMs > 0) usleep($sleepMs * 1000);
        }
        usort($out, fn($a, $b) => strcmp($a['ts'], $b['ts']));
        return $out;
    }




    ############################################################################
    public function handle()
    {

        $acao = $this->argument('acao');




        $cid = 'UCj-RTZE-V3Q6jleatRR9k2A';
        $urls = $this->patternsFromChannelId($cid);
        dump($urls);
        $captures = $this->mergeTimestamps($urls);
        $samples10 = $this->sampleEvenlyByTime($captures, 10);
        $points  = $this->scrape_samples_subscribers($samples10, 200);
        dd($points);


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
