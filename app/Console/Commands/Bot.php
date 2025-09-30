<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use HeadlessChromium\Dom\Node;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use HeadlessChromium\BrowserFactory;
use Illuminate\Support\Facades\Http;
use HeadlessChromium\Communication\Message;

class Bot extends Command
{

    protected $signature = 'bot {acao?}';

    protected function curlGetWithTimeout(string $url, int $timeout = 10, int $connectTimeout = 5, int $retries = 2): ?string
    {
        $attempt = 0;
        $backoffMs = 400;

        while ($attempt < $retries) {
            $attempt++;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,

                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,     // Wayback redireciona
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_CONNECTTIMEOUT => $connectTimeout, // tempo p/ conectar (DNS+TCP+TLS)
                CURLOPT_TIMEOUT        => $timeout,        // tempo total p/ resposta
                #CURLOPT_LOW_SPEED_LIMIT => 1024,    // se cair < 1KB/s...
                #CURLOPT_LOW_SPEED_TIME => 5,       // ...por 5s, aborta (timeout)
                CURLOPT_ENCODING       => '',      // aceita gzip/deflate/br
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; GarimpIA/1.0)',
                CURLOPT_HTTPHEADER     => [
                    #'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept: application/json', // CDX responde JSON
                    'Accept-Language: en-US,en;q=0.9,pt-BR;q=0.8',
                ],
                // Opcional (mantém TLS padrão):
                #CURLOPT_SSL_VERIFYHOST => 2,
                #CURLOPT_SSL_VERIFYPEER => true,
                // Keep-alive (se libcurl suportar):
                // CURLOPT_TCP_KEEPALIVE => 1, CURLOPT_TCP_KEEPIDLE => 30, CURLOPT_TCP_KEEPINTVL => 10,
            ]);


            // PROXY HTTP com CONNECT (Bright Data)
            #######curl_setopt($ch, CURLOPT_PROXY, 'brd.superproxy.io:33335');
            #curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            #curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true); // HTTPS via CONNECT
            ########curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'brd-customer-hl_6b96b01d-zone-residential_proxy1:d95jqk7ho7at');



            $body = curl_exec($ch);
            $errno = curl_errno($ch);
            $err   = curl_error($ch);
            $code  = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            // Sucesso
            if ($errno === 0 && $code >= 200 && $code < 400 && is_string($body) && $body !== '') {
                return $body;
            }

            // Se 429/5xx ou timeout -> retry com backoff
            $retryable =
                $errno === CURLE_OPERATION_TIMEDOUT ||
                $errno === CURLE_COULDNT_CONNECT   ||
                $errno === CURLE_COULDNT_RESOLVE_HOST ||
                $code === 429 || ($code >= 500 && $code < 600);

            if ($retryable && $attempt < $retries) {
                usleep(($backoffMs + random_int(50, 250)) * 1000);
                $backoffMs *= 2;
                continue;
            }

            // Falha final
            echo 'url:' . $url . 'attempt:' . $attempt . 'errno' . $errno . 'error' . $err . 'code' . $code;
            return null;
        }

        return null;
    }



    private function montaURLParamsCDXList(string $url, int $ano, int $limit = 10, bool $prefix = false): string
    {
        $base = 'https://web.archive.org/cdx/search/cdx';

        // Intervalo do ano (1 a 14 dígitos é aceito; usamos o máximo de precisão)
        $from = sprintf('%04d0101', $ano); // YYYY-01-01 00:00:00
        $to   = sprintf('%04d1231', $ano); // YYYY-12-31 23:59:59

        // Se quiser capturar subcaminhos, habilite prefix:
        if ($prefix && !str_ends_with($url, '/*')) {
            $url = rtrim($url, '/') . '/*';
        }

        // Params “normais” via http_build_query
        $params = [
            'url'      => $url,
            'output'   => 'json',
            'fl'       => 'timestamp,original,statuscode',
            'from'     => $from,
            'to'       => $to,
            'collapse' => 'digest',
            'showDupeCount' => true,
            'limit'    => $limit,         // 3 ou 4 como você pediu
            'matchType' => $prefix ? 'prefix' : 'exact', // opcional; com /* já vira prefix
            // 'gzip' => 'false', // só se você for baixar com cURL cru e não quiser lidar com gzip
        ];

        // Monta a query base
        $q = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        // Anexa filtros como “filter=...” repetidos (sem colchetes)
        #foreach (['statuscode:200', 'mimetype:text/html'] as $f) {
        foreach (['statuscode:200',] as $f) {
            $q .= '&filter=' . rawurlencode($f);
        }

        return $base . '?' . $q;
    }






    /**
     * Converte a saída CDX (texto) em uma lista de snapshots id_ prontos para crawl.
     *
     * @param string   $cdxText  Texto do CDX (linhas como as que você colou)
     * @param int|null $ano      Se informado, mantém só capturas desse ano
     * @param int      $limit    Máximo de snapshots a retornar
     * @param bool     $onlyChannelUrlkey  Se true, filtra urlkey começando com com,youtube)/channel/
     * @return array   Lista de strings (snapshots id_) + rows filtradas (opcional)
     */
    private function cdxPlainToSnapshots(string $cdxText, ?int $ano = null, int $limit = 6, bool $onlyChannelUrlkey = true): array
    {
        $rows = [];

        foreach (preg_split('/\R+/', trim($cdxText)) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '[' || $line[0] === '{') continue; // ignora json
            // Quebra em no máx. 7 partes: urlkey ts original mimetype status digest length
            $parts = preg_split('/\s+/', $line, 7);
            if (count($parts) < 6) continue;

            [$urlkey, $ts, $orig, $mime, $status, $digest] = array_slice($parts, 0, 6);

            // (opcional) só linhas de canal /channel/
            if ($onlyChannelUrlkey && stripos($urlkey, 'com,youtube)/channel/') !== 0) {
                continue;
            }
            // ano
            if ($ano !== null && substr($ts, 0, 4) !== (string)$ano) {
                continue;
            }
            // filtros principais
            if ($status !== '200') continue;
            if (stripos($mime, 'text/html') !== 0) continue;

            // Normaliza original: garante esquema
            if (!preg_match('~^https?://~', $orig)) {
                $orig = 'http://' . ltrim($orig, '/');
            }

            $rows[] = [
                'urlkey' => $urlkey,
                'ts'     => $ts,
                'orig'   => $orig,
                'mime'   => $mime,
                'status' => $status,
                'digest' => $digest,
            ];
        }

        if (!$rows) return [];

        // Colapsa por digest (última ocorrência vence; troque se quiser “primeira”)
        $byDigest = [];
        foreach ($rows as $r) {
            $byDigest[$r['digest']] = $r;
        }
        $rows = array_values($byDigest);

        // Ordena: por proximidade do meio do ano, senão por timestamp crescente
        if ($ano !== null) {
            $target = (int) sprintf('%04d0701000000', $ano);
            usort($rows, function ($a, $b) use ($target) {
                return abs((int)$a['ts'] - $target) <=> abs((int)$b['ts'] - $target);
            });
        } else {
            usort($rows, fn($a, $b) => strcmp($a['ts'], $b['ts']));
        }

        // Limita N
        $rows = array_slice($rows, 0, $limit);

        // Monta snapshots id_
        $snapshots = array_map(function ($r) {
            // Garante id_ (independe de js_/im_/etc.)
            return preg_replace(
                '~^https?://web\.archive\.org/web/(\d{1,14})(?:[a-z]{1,3}_|\*)?/(.+)$~',
                #'https://web.archive.org/web/$1id_/$2',
                'https://web.archive.org/web/$1/$2',
                "https://web.archive.org/web/{$r['ts']}/{$r['orig']}"
            );
        }, $rows);

        return $snapshots; // se quiser, retorne ['snapshots'=>$snapshots, 'rows'=>$rows]
    }





    private function preparaSnapshotsEspacados(string $cdxHtml, int $ano, int $limit = 3, int $minMonthsGap = 4): array
    {
        // 1) Extrai o JSON de dentro do <pre> (ou do próprio HTML)
        $jsonStr = trim($cdxHtml);
        if ($jsonStr === '') return [];

        if ($jsonStr[0] !== '[') {
            if (preg_match('~<pre[^>]*>\s*(\[.*\])\s*</pre>~', $jsonStr, $m)) {
                $jsonStr = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                // fallback: do primeiro [ ao último ]
                $p1 = strpos($jsonStr, '[');
                $p2 = strrpos($jsonStr, ']');
                if ($p1 === false || $p2 === false || $p2 <= $p1) return [];
                $jsonStr = html_entity_decode(substr($jsonStr, $p1, $p2 - $p1 + 1), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        $rows = json_decode($jsonStr, true);
        if (!is_array($rows) || count($rows) < 2) return [];

        // 2) Descobre o índice das colunas
        $header = $rows[0];
        $idxTs   = array_search('timestamp', $header, true);
        $idxOrig = array_search('original',  $header, true);
        $idxCode = array_search('statuscode', $header, true);
        if ($idxTs === false || $idxOrig === false || $idxCode === false) return [];

        // 3) Filtra: ano + status 200. Ordena por timestamp asc e dedup por ts.
        $items = [];
        for ($i = 1; $i < count($rows); $i++) {
            $ts   = (string) ($rows[$i][$idxTs]   ?? '');
            $orig = (string) ($rows[$i][$idxOrig] ?? '');
            $code = (string) ($rows[$i][$idxCode] ?? '');

            if ($ts === '' || $orig === '' || $code !== '200') continue;
            if (substr($ts, 0, 4) !== (string)$ano) continue;

            $items[$ts] = [ // dedup por TS
                'ts'      => $ts,
                'date'    => Carbon::createFromFormat('YmdHis', str_pad($ts, 14, '0'), 'UTC')->toDateString(),
                'original' => $orig,
            ];
        }

        if (!$items) return [];

        // ordena por TS asc
        $items = array_values($items);
        usort($items, fn($a, $b) => strcmp($a['ts'], $b['ts']));

        // 4) Seleciona até 3 com espaçamento mínimo de 4 meses (~120 dias)
        $minDays = (int) round($minMonthsGap * 30); // aproximação boa p/ espaçamento
        $picked = [];

        foreach ($items as $row) {
            if (count($picked) === 0) {
                $picked[] = $row;
                if (count($picked) >= $limit) break;
                continue;
            }
            $last = end($picked);
            $d1 = Carbon::createFromFormat('YmdHis', str_pad($last['ts'], 14, '0'), 'UTC');
            $d2 = Carbon::createFromFormat('YmdHis', str_pad($row['ts'],  14, '0'), 'UTC');

            if ($d1->diffInDays($d2) >= $minDays) {
                $picked[] = $row;
                if (count($picked) >= $limit) break;
            }
        }

        // Caso só tenham 2 registros no ano:
        // - se a diferença >= 4 meses, fica 2; senão, mantemos 1 (o mais cedo).
        if (count($picked) === 1 && count($items) === 2) {
            $d1 = Carbon::createFromFormat('YmdHis', str_pad($items[0]['ts'], 14, '0'), 'UTC');
            $d2 = Carbon::createFromFormat('YmdHis', str_pad($items[1]['ts'], 14, '0'), 'UTC');
            if ($d1->diffInDays($d2) >= $minDays) {
                $picked = [$items[0], $items[1]];
            }
        }

        // 5) Monta snapshots id_
        foreach ($picked as &$p) {
            $p['snapshot'] = preg_replace(
                '~^https?://web\.archive\.org/web/(\d{1,14})(?:[a-z]{1,3}_|\*)?/(.+)$~',
                #'https://web.archive.org/web/$1id_/$2',
                'https://web.archive.org/web/$1/$2',
                "https://web.archive.org/web/{$p['ts']}/{$p['original']}"
            );
        }
        unset($p);

        return $picked;
    }





    ######################################################################


    // protected function fetchSubsFromWayback(string $url, int $ano)
    // {

    //     $seletor1 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.yt-uix-tooltip';
    //     $seletor2 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.subscribed.yt-uix-tooltip';
    //     $seletor3 = 'span.yt-core-attributed-string.yt-content-metadata-view-model-wiz__metadata-text yt-core-attributed-string--white-space-pre-wrap.yt-core-attributed-string--link-inherit-color';
    //     $seletor4 = 'span.yt-core-attributed-string.yt-content-metadata-view-model-wiz__metadata-text yt-core-attributed-string--white-space-pre-wrap.yt-core-attributed-string--link-inherit-color > span';
    //     $seletor5 = '#subscriber-count.style-scope.ytd-c4-tabbed-header-renderer'; // aria-label
    //     $seletor6 = 'span.yt-subscription-button-subscriber-count-branded-horizontal.subscribed'; // gettext

    //     $subscribers = null;

    //     // if ($elem = $page->dom()->querySelector($seletor1)) {
    //     //     $subscribers = $this->subs_to_int($elem->getAttribute('title'));
    //     // } elseif ($elem = $page->dom()->querySelector($seletor2)) {
    //     //     $subscribers = $this->subs_to_int($elem->getAttribute('title'));
    //     // } elseif ($elem = $page->dom()->querySelector($seletor3)) {
    //     //     $subscribers = $this->subs_to_int($elem->getText());
    //     // } elseif ($elem = $page->dom()->querySelector($seletor4)) {
    //     //     $subscribers = $this->subs_to_int($elem->getText());
    //     // } elseif ($elem = $page->dom()->querySelector($seletor5)) {
    //     //     $subscribers = $this->subs_to_int($elem->getAttribute('aria-label'));
    //     // } elseif ($elem = $page->dom()->querySelector($seletor6)) {
    //     //     $subscribers = $this->subs_to_int($elem->getText());
    //     // } else {


    //     $regexes = [
    //         '"subscriberCountText":{"runs":\[{"text":"(.+?) subscribers"}',
    //         '{"text":{"content":"([\d\.KkMm]+?) subscribers"}}',
    //     ];

    //     $regexes = [
    //         // "subscriberCountText":{"runs":[{"text":"75,114 subscribers"}]}
    //         '"subscriberCountText"\s*:\s*\{\s*"runs"\s*:\s*\[\s*\{\s*"text"\s*:\s*"([^"]+?)\s*(?:subscribers|inscritos)"',
    //         // "subscriberCountText":{"simpleText":"75,114 subscribers"}
    //         '"subscriberCountText"\s*:\s*\{\s*"simpleText"\s*:\s*"([^"]+?)\s*(?:subscribers|inscritos)"',
    //         // {"text":{"content":"75,114 subscribers"}}
    //         '"text"\s*:\s*\{\s*"content"\s*:\s*"([^"]+?)\s*(?:subscribers|inscritos)"',

    //         // 2) Depois tenta HTML (atributos e conteúdo do span)
    //         // aria-label="75,114 subscribers"  (também cobre "inscritos")
    //         'aria-label="([\d\.,KkMm]+)\s*(?:subscribers|inscritos)"',
    //         // data-tooltip-text="75,114"
    //         'data-tooltip-text="([\d\.,KkMm]+)"',
    //         // title="75,114"
    //         'title="([\d\.,KkMm]+)"',
    //         // <span class="... yt-subscription-button ...">75,114</span>
    //         '<span[^>]*class="[^"]*yt-subscription-button[^"]*[^>]*>([\d\.,KkMm]+)\s*<\/span>',
    //         // #subscriber-count aria-label="75,114 subscribers"
    //         'id="subscriber-count"[^>]*aria-label="([\d\.,KkMm]+)\s*(?:subscribers|inscritos)"',
    //         // #subscriber-count>75,114 (com ou sem palavra)
    //         'id="subscriber-count"[^>]*>\s*([\d\.,KkMm]+)\s*(?:subscribers|inscritos)?\s*<\/span>',
    //     ];
    // }



    protected function extractSubs(string $html)
    {
        $regexes = [
            // aria-label no #subscriber-count
            '~\bid="subscriber-count"[^>]*\saria-label="([\d\.\, \x{00A0}\x{202F}KkMm]+)\s*(?:subscribers|inscritos)\b"~iu',
            // texto interno do yt-formatted-string
            '~<yt-formatted-string[^>]*\bid="subscriber-count"[^>]*>\s*([\d\.\, \x{00A0}\x{202F}KkMm]+)\s*(?:subscribers|inscritos)?\s*</yt-formatted-string>~iu',
        ];

        $htmlNorm = preg_replace("/\x{00A0}|\x{202F}/u", ' ', $html);
        echo "\n\n\n$htmlNorm";


        foreach ($regexes as $re) {
            if (preg_match($re, $htmlNorm, $m)) {
                dump($m);
                $subs = $this->subs_to_int($m[1]);
                if ($subs > 0)
                    return $subs;
            }
        }
        return null;

        // ## no HTML
        // foreach ($regexes as $k => $re) {
        //     if (preg_match($re, $htmlNorm, $m)) {
        //         dump($m);
        //         $subscribers = $this->subs_to_int($m[1]); // ex: "19.5K"
        //         if ($subscribers > 0) {
        //             echo "\nCaiu na regx HTML $k => {$re}";
        //             return (int) $subscribers;
        //         }
        //     }
        // }
        // return null;
    }


    function subs_to_int(string $txt): ?int
    {
        echo "\n\nEntrando na subs_to_int=======================\n";
        dump($txt);
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
                $res = (int) round($val * $mult);
                echo "\n\nSaida da subs_to_int: $res";
                return $res;
            } else {
                $digits = preg_replace('/\D+/', '', $num);
                if ($digits !== '' && ctype_digit($digits)) {
                    $res = (int) $digits;
                    echo "\n\nSaida da subs_to_int: $res";
                    return $res;
                }
            }
        }
        return null;
    }


    public function getAliasUrlChannelAndYears(string $cid)
    {
        $apiKey = env('YOUTUBE_API_KEY');
        $base   = 'https://www.googleapis.com/youtube/v3/channels';

        // Uma única chamada: pega customUrl (p/ @handle), publishedAt e subscriberCount
        $resp = Http::get($base, [
            'part'   => 'snippet,statistics',
            'id'     => $cid,
            'fields' => 'items(id,snippet(customUrl,publishedAt,title),statistics(subscriberCount))',
            'key'    => $apiKey,
        ]);

        // URLs base (sempre inclui por ID)
        $urls = [];
        $push = function (string $u) use (&$urls) {
            if (!in_array($u, $urls, true)) $urls[] = $u;
        };

        $push("https://www.youtube.com/channel/{$cid}");
        // $push("https://www.youtube.com/channel/{$cid}/about");

        if ($resp->ok()) {
            $item = data_get($resp->json(), 'items.0');
            $customUrl   = data_get($item, 'snippet.customUrl');       // pode vir "@handle" ou slug legado
            $publishedAt = data_get($item, 'snippet.publishedAt');     // ISO8601
            $subsToday   = data_get($item, 'statistics.subscriberCount'); // string numérica; pode não vir se oculto

            // Se o customUrl já vier como "@handle", adiciona a URL por handle
            if (is_string($customUrl) && Str::startsWith($customUrl, '@')) {
                $handle = ltrim($customUrl, '@');
                $push("https://www.youtube.com/@{$handle}");
                // $push("https://www.youtube.com/@{$handle}/about");
            }

            // Monta a série: criação (0) e hoje (subs atuais ou 0 se ausente)
            $series = [];

            if ($publishedAt) {
                $d0 = Carbon::parse($publishedAt)->toDateString();
                $series[] = [
                    'data'      => $d0,
                    'ano'       => (int) substr($d0, 0, 4),
                    'inscritos' => 0,
                    'url'       => '', // sem URL nos extremos, como pedido
                ];
            }

            $today = Carbon::now()->toDateString();
            $series[] = [
                'data'      => $today,
                'ano'       => (int) substr($today, 0, 4),
                'inscritos' => isset($subsToday) ? (int) $subsToday : 0, // se oculto, cai pra 0
                'url'       => '',
            ];

            return [
                'urls'   => $urls,
                'series' => $series,
            ];
        } else {
            echo "\nErro na chamada API";
        }
    }



    private function getHTMLFromHeadless(string $url, int $timer = 5, array $extraHeaders = [], ?string $userAgent = null): ?string
    {
        $browserFactory = new BrowserFactory();

        $browser = $browserFactory->createBrowser([
            'headless'    => true,
            'windowSize'  => [1920, 1080],
            'noSandbox'   => true,
            'customFlags' => ['--lang=pt-BR'],
            // 'customFlags' => ['--proxy-server=http://host:port'],
        ]);

        try {
            $page = $browser->createPage();

            // --- Habilita Network e define headers extras para TODAS as requests da página
            $session = $page->getSession();
            $session->sendMessageSync(new Message('Network.enable'));

            // headers padrão + sobrescritos
            $defaultHeaders = [
                #'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',


                'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control'   => 'no-cache',
                'Pragma'          => 'no-cache',
            ];
            $headers = array_merge($defaultHeaders, $extraHeaders);

            $session->sendMessageSync(new Message('Network.setExtraHTTPHeaders', [
                'headers' => $headers,
            ]));

            // --- (Opcional) Override do User-Agent (e idioma de aceitação)
            if ($userAgent) {
                $session->sendMessageSync(new Message('Emulation.setUserAgentOverride', [
                    'userAgent'      => $userAgent,
                    'acceptLanguage' => $headers['Accept-Language'] ?? 'pt-BR,pt;q=0.9,en-US;q=0.8',
                    'platform'       => 'Windows', // ou 'Linux', 'MacIntel'
                ]));
            }

            echo "\n\n\n{$url}\n\n";

            // Navega e espera rede ficar ociosa (melhor que sleep puro)
            $page->navigate($url);
            // NETWORK_IDLE precisa do import HeadlessChromium\Page
            #$nav->waitForNavigation(Page::NETWORK_IDLE, 15000); // 15s
            // fallback se quiser
            if ($timer > 0) {
                sleep($timer);
            }

            echo "\nPassou - {$timer}s: {$url}";

            $html = $page->getHtml();
            return $html;
        } catch (\Throwable $e) {
            echo "\n\nException : " . $e->getMessage();
            return null;
        } finally {
            $browser->close();
        }
    }


    function ddmmFromWaybackUrl(string $url): ?string
    {
        // 1) tenta capturar o timestamp na URL do Wayback
        if (preg_match('~^https?://web\.archive\.org/web/(\d{8,14})(?:[a-z]{1,3}_|\*)?/~i', $url, $m)) {
            $ts = $m[1];
        }
        // 2) ou aceita um timestamp “seco”
        elseif (preg_match('~^(\d{8,14})$~', $url, $m)) {
            $ts = $m[1];
        } else {
            return null;
        }

        // precisa ter ao menos YYYYMMDD
        if (strlen($ts) < 8) {
            return null;
        }

        $dd = substr($ts, 6, 2);
        $mm = substr($ts, 4, 2);
        return "{$dd}-{$mm}";
    }


    private function getClosestURL(string $url, $ano)
    {


        // Usa um timestamp no MEIO do ano para reduzir “closest” de anos vizinhos
        $tsMid = sprintf('%04d0707000000', $ano); // 
        $availableUrl = "https://archive.org/wayback/available?url=" . urlencode($url) . "&timestamp={$tsMid}&output=json";

        $timer = 1;
        $html_json = $this->getHTMLFromHeadless($availableUrl, $timer, ['Accept' => 'application/json,text/plain;q=0.9,*/*;q=0.8']);
        dump($html_json);

        $html_curl = $this->curlGetWithTimeout($availableUrl);
        dump($html_curl);

        return $html_json;
        #dd($json);
    }


    private function validateClosest(string $availableHtml, int $ano): ?string
    {
        if (trim($availableHtml) === '') {
            echo "\nvalidateClosest: vazio";
            return null;
        }

        $jsonStr = $availableHtml;

        // 1) Se não parece JSON puro, tenta extrair do <pre>…</pre>
        if ($jsonStr[0] !== '{') {
            // Tenta pegar exatamente o conteúdo do <pre>
            if (preg_match('~<pre[^>]*>\s*(\{.*\})\s*</pre>~is', $availableHtml, $m)) {
                // Decodifica entidades HTML, caso venham escapadas
                $jsonStr = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                // Fallback: pega do primeiro { até o último }
                $p1 = strpos($availableHtml, '{');
                $p2 = strrpos($availableHtml, '}');
                if ($p1 !== false && $p2 !== false && $p2 > $p1) {
                    $jsonStr = substr($availableHtml, $p1, $p2 - $p1 + 1);
                    $jsonStr = html_entity_decode($jsonStr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } else {
                    echo "\nvalidateClosest: JSON não encontrado no HTML";
                    return null;
                }
            }
        }

        // 2) Sanitiza: remove BOM e XSSI guard
        $jsonStr = preg_replace('/^\xEF\xBB\xBF/', '', $jsonStr);          // BOM
        $jsonStr = ltrim($jsonStr);
        $jsonStr = preg_replace('/^\)\]\}\'?,?/', '', $jsonStr);           // )]}'

        // 3) Decodifica
        $availableJson = json_decode($jsonStr, true);
        if (!is_array($availableJson)) {
            echo "\nvalidateClosest: json_decode falhou: " . json_last_error_msg();
            return null;
        }

        // 4) Valida presença do closest e disponibilidade
        $closest = data_get($availableJson, 'archived_snapshots.closest');
        if (!$closest || !data_get($closest, 'available')) {
            echo "\nvalidateClosest: sem snapshot closest";
            return null;
        }

        // 5) Valida ANO do timestamp
        $closestTs = (string) data_get($closest, 'timestamp'); // ex: 20180227172601
        if (!$closestTs || strlen($closestTs) < 4) {
            echo "\nvalidateClosest: timestamp inválido";
            return null;
        }
        $closestAno = (int) substr($closestTs, 0, 4);
        if ($closestAno !== (int) $ano) {
            echo "\nvalidateClosest: skip ts={$closestTs} (ano {$closestAno}) != {$ano}";
            return null;
        }

        // 6) Retorna URL do snapshot
        $closestSnapshotUrl = (string) data_get($closest, 'url');
        if ($closestSnapshotUrl === '') {
            echo "\nvalidateClosest: sem URL de snapshot";
            return null;
        }

        return $closestSnapshotUrl;
    }


    private function poeIdNaURL($url)
    {

        // se já está em id_, não faz nada
        if (!preg_match('~^https?://web\.archive\.org/web/\d{1,14}id_/~', $url)) {
            // força https e injeta id_
            $url = preg_replace(
                '~^https?://web\.archive\.org/web/(\d{1,14})(?:[a-z]{1,3}_|\*)?/(.+)$~',
                'https://web.archive.org/web/$1id_/$2',
                $url
            );
        }
        return $url;
    }


    public function processaTodasURLs(array $urls_years): array
    {
        $urls   = $urls_years['urls']   ?? [];
        $series = $urls_years['series'] ?? [];

        // 1) Descobre ano de criação (menor data da série) e ano atual
        $anoHoje = (int) Carbon::now()->year;
        $ano0    = $anoHoje;

        $anosJaNaSerie = [];
        foreach ($series as $p) {
            if (!empty($p['data'])) {
                $y = (int) substr($p['data'], 0, 4);
                $anosJaNaSerie[$y] = true;
                if ($y < $ano0)
                    $ano0 = $y;
            }
        }

        // 2) Conjunto de anos faltantes (inclusive extremos); depois removemos os que já existem
        $anosRestantes = array_fill_keys(range($ano0, $anoHoje), true);
        foreach (array_keys($anosRestantes) as $y) {
            if (isset($anosJaNaSerie[$y])) {
                unset($anosRestantes[$y]);
            }
        }

        if (empty($anosRestantes)) {
            // Nada a preencher — só ordena e retorna
            usort($series, fn($a, $b) => strcmp($a['data'], $b['data']));
            return ['urls' => $urls, 'series' => $series];
        }

        // 3) Prioriza /channel/UC... antes de @handle
        usort($urls, function ($a, $b) {
            $rank = fn($u) => str_contains($u, '/channel/') ? 0 : (str_contains($u, '/@') ? 1 : 2);
            return $rank($a) <=> $rank($b);
        });

        dump($anosRestantes);

        // 4) Varre URLs; para cada URL, tenta apenas anos que ainda faltam
        foreach ($urls as $url) {
            if (empty($anosRestantes))
                break;

            foreach (array_keys($anosRestantes) as $ano) {

                # ####################### linhas do closest ############
                $availableHtml = $this->getClosestURL($url, $ano);
                $url_to_crawl = $this->validateClosest($availableHtml, $ano);

                $dd_mm = "12-12";
                if ($url_to_crawl) {
                    echo "\n$url_to_crawl";
                }

                $url_cdx_list = $this->montaURLParamsCDXList($url, $ano);
                echo "\n$url_cdx_list";
                #$jsonCdxList = $this->getHTMLFromHeadless($url_cdx_list);
                $jsonCdxList = $this->curlGetWithTimeout($url_cdx_list);
                #dump($jsonCdxList);
                #$url_to_crawl = false;
                // if ($jsonCdxList) {
                //     $listaSnapshots = $this->cdxPlainToSnapshots($jsonCdxList);
                //     dump($listaSnapshots);
                // }


              
                if ($jsonCdxList) {
                    $url_snapshots = $this->preparaSnapshotsEspacados($jsonCdxList, $ano);
                    $timer = 40;
                    dump($url_snapshots);
                    foreach ($url_snapshots as $url_snapshot) {
                        $url_to_crawl = $url_snapshot['snapshot'];
                        dump($url_to_crawl);
                        #$html = $this->curlGetWithTimeout($url_to_crawl);
                        echo "\nurl_to_crawl:\n\n$url_to_crawl";
                        $dd_mm = $this->ddmmFromWaybackUrl($url_to_crawl);

                        $html = $this->getHTMLFromHeadless($url_to_crawl, $timer);

                        if ($html) {
                            $inscritos = $this->extractSubs($html);

                            echo "\n\n\n\n$inscritos\n\n\n";

                            if ($inscritos > 0) {
                                #$snapUrl = $this->resolveSnapshotUrlSameYear($url, $ano); // comente para evitar chamada extra
                                $series[] = [
                                    'data'      => "{$ano}-{$dd_mm}", // ou use a data real do snapshot se quiser
                                    'ano'       => $ano,
                                    'inscritos' => $inscritos,
                                    'url'       => $url_to_crawl, 
                                ];
                                unset($anosRestantes[$ano]); // “mata” o ano; não tenta em outras URLs
                            }
                            if (empty($anosRestantes))
                                break;
                        }
                    }
                }

                // if ($url_to_crawl) {
                //     $url_to_crawl = $this->poeIdNaURL($url_to_crawl);
                //     echo "\n\n$url_to_crawl";
                //     $timer = 5;
                //     #$html = $this->getHTMLFromHeadless($url_to_crawl, $timer);
                //     $html = $this->curlGetWithTimeout($url_to_crawl);
                //     if ($html) {
                //         $inscritos = $this->extractSubs($html);
                //         if ($inscritos > 0) {
                //             #$snapUrl = $this->resolveSnapshotUrlSameYear($url, $ano); // comente para evitar chamada extra
                //             $series[] = [
                //                 #'data'      => "{$ano}-12-31", // ou use a data real do snapshot se quiser
                //                 'ano'       => $ano,
                //                 'inscritos' => $inscritos,
                //                 #'url'       => $snapUrl ?? '', // extremos ficam '', aqui podemos anexar o snapshot
                //             ];
                //             unset($anosRestantes[$ano]); // “mata” o ano; não tenta em outras URLs
                //         }
                //         if (empty($anosRestantes)) break;
                //     }
                // }

            }
        }

        // 5) Ordena por data e retorna no mesmo formato
        usort($series, fn($a, $b) => strcmp($a['data'], $b['data']));
        return ['urls' => $urls, 'series' => $series];
    }


    ############################################################################
    public function handle()
    {

        $cid = "UCsra3f6ogpXhIZbSUe2OoaA"; #baster
        $cid = "UC04BY9XdbTltt3PYOaGGMkA"; #bolinha


        $urls_years = $this->getAliasUrlChannelAndYears($cid);

        $res = $this->processaTodasURLs($urls_years);
        #$res = $this->processaTodasURLsViaCDX($urls_years);

        dd($res);
    }
}
