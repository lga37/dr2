<?php

#use App\Livewire\Monet;
use App\Http\Controllers\ProfileController;
use App\Livewire\Arxiv;
use App\Livewire\Busca;
use App\Livewire\Canal;
use App\Livewire\Comentario;
use App\Livewire\Graf;
use App\Livewire\Monet;
use App\Livewire\Monetizacao;
use App\Livewire\Nlp;
use App\Livewire\Polarizacao;
use App\Livewire\Resultados;
use App\Livewire\Tarefa1;
use App\Livewire\Tarefa2;
use App\Livewire\Tarefa3;
use App\Livewire\Tarefa4;
use App\Livewire\Tese;
use App\Livewire\Toxic;
use App\Livewire\Toxicidade;
use App\Livewire\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;


################################# INICIO BENCHMARK ####################################
#######################################################################################


Route::get('/debug-channel-desc', function () {

    $videos = json_decode(
        file_get_contents(storage_path('app/bench/polarization_videos.json')),
        true
    );

    $channels = [];

    foreach ($videos as $v) {
        $ch = $v['channelId'];

        if (!isset($channels[$ch])) {
            $channels[$ch] = [
                'handle' => $v['seed_handle'] ?? '',
                'title' => $v['channelTitle'] ?? '',
                'desc' => $v['channelDesc'] ?? '',
            ];
        }
    }

    echo "<pre>";

    foreach ($channels as $ch => $c) {

        echo "==============================\n";
        echo "Channel: {$c['handle']}\n";
        echo "Title:   {$c['title']}\n";
        echo "------------------------------\n";
        echo $c['desc'] . "\n\n";
    }

    echo "</pre>";
});


Route::get('/ds-links', function () {

    $path = storage_path('app/bench/polarization_videos.json');
    $videos = json_decode(file_get_contents($path), true);

    $socialDomains = [
        'facebook.com', 'instagram.com', 'twitter.com', 'x.com',
        'youtube.com', 'youtu.be', 'tiktok.com', 'linkedin.com',
        'telegram.me', 't.me', 'whatsapp.com', 'wa.me',
        'discord.gg', 'discord.com'
    ];

    $moneyDomains = [
        'paypal.com', 'patreon.com', 'padrim.com.br',
        'apoia.se', 'buymeacoffee.com', 'ko-fi.com',
        'picpay.me', 'picpay.com', 'catarse.me',
        'benfeitoria.com', 'substack.com', 'locals.com',
        'stripe.com', 'mercadopago.com', 'pagseguro.uol.com.br',
        'pixabay.com', 'streamlabs.com', 'superchat.live'
    ];

    $shorteners = [
        'bit.ly', 'tinyurl.com', 't.co', 'goo.gl',
        'ow.ly', 'buff.ly', 'is.gd', 'cutt.ly',
        'shorturl.at', 'rebrand.ly', 'lnkd.in', 'linktr.ee'
    ];

    $moneyTextPatterns = [
        // português
        '/\bpix\b/i',
        '/chave\s*pix/i',
        '/doa[cç][aã]o/i',
        '/doar/i',
        '/apoie/i',
        '/apoiar/i',
        '/apoio/i',
        '/contribua/i',
        '/contribui[cç][aã]o/i',
        '/financiamento\s+coletivo/i',
        '/vaquinha/i',
        '/colabore/i',
        '/membro/i',
        '/seja\s+membro/i',

        // inglês
        '/\bdonate\b/i',
        '/donation/i',
        '/support\s+us/i',
        '/support\s+the\s+channel/i',
        '/become\s+a\s+member/i',
        '/membership/i',
        '/crowdfunding/i',
        '/contribute/i',
        '/tip\s+jar/i',
        '/sponsor/i',

        // cripto
        '/bitcoin/i',
        '/\bbtc\b/i',
        '/ethereum/i',
        '/\beth\b/i',
        '/crypto/i',
        '/cripto/i',
        '/wallet/i',
        '/carteira/i',
    ];

    $result = [];

    foreach ($videos as $v) {
        $ch = $v['channelId'] ?? 'sem_channel_id';

        if (!isset($result[$ch])) {
            $result[$ch] = [
                'handle' => $v['seed_handle'] ?? '',
                'channelTitle' => $v['channelTitle'] ?? '',
                'lang' => $v['seed_language'] ?? $v['lang'] ?? '',

                'video_count' => 0,

                // vídeo: soma para média
                'video_urls_total_sum' => 0,
                'video_social_sum' => 0,
                'video_money_sum' => 0,
                'video_short_sum' => 0,
                'video_other_sum' => 0,
                'video_money_text_sum' => 0,

                // canal: URLs únicas da descrição do canal
                'channel_urls_total' => [],
                'channel_social' => [],
                'channel_money' => [],
                'channel_short' => [],
                'channel_other' => [],
                'channel_money_text' => 0,

                'channel_desc_processed' => false,
            ];
        }

        $result[$ch]['video_count']++;

        // 1) DESCRIÇÃO DO VÍDEO — calcula por vídeo e soma
        $videoClass = classifyTextUrls(
            $v['videoDesc'] ?? '',
            $socialDomains,
            $moneyDomains,
            $shorteners,
            $moneyTextPatterns
        );

        $result[$ch]['video_urls_total_sum'] += $videoClass['total_count'];
        $result[$ch]['video_social_sum'] += $videoClass['social_count'];
        $result[$ch]['video_money_sum'] += $videoClass['money_count'];
        $result[$ch]['video_short_sum'] += $videoClass['short_count'];
        $result[$ch]['video_other_sum'] += $videoClass['other_count'];
        $result[$ch]['video_money_text_sum'] += $videoClass['money_text_hit'];

        // 2) DESCRIÇÃO DO CANAL — processa uma vez por canal
        if (!$result[$ch]['channel_desc_processed']) {
            $channelClass = classifyTextUrls(
                $v['channelDesc'] ?? '',
                $socialDomains,
                $moneyDomains,
                $shorteners,
                $moneyTextPatterns
            );

            foreach ($channelClass['total'] as $url) {
                $result[$ch]['channel_urls_total'][$url] = true;
            }

            foreach ($channelClass['social'] as $url) {
                $result[$ch]['channel_social'][$url] = true;
            }

            foreach ($channelClass['money'] as $url) {
                $result[$ch]['channel_money'][$url] = true;
            }

            foreach ($channelClass['short'] as $url) {
                $result[$ch]['channel_short'][$url] = true;
            }

            foreach ($channelClass['other'] as $url) {
                $result[$ch]['channel_other'][$url] = true;
            }

            $result[$ch]['channel_money_text'] = $channelClass['money_text_hit'];
            $result[$ch]['channel_desc_processed'] = true;
        }
    }

    foreach ($result as &$r) {
        $q = max(1, $r['video_count']);

        // médias por vídeo
        $r['video_urls_avg'] = $r['video_urls_total_sum'] / $q;
        $r['video_social_avg'] = $r['video_social_sum'] / $q;
        $r['video_money_avg'] = $r['video_money_sum'] / $q;
        $r['video_short_avg'] = $r['video_short_sum'] / $q;
        $r['video_other_avg'] = $r['video_other_sum'] / $q;
        $r['video_money_text_avg'] = $r['video_money_text_sum'] / $q;

        // contagens canal
        $r['channel_urls_count'] = count($r['channel_urls_total']);
        $r['channel_social_count'] = count($r['channel_social']);
        $r['channel_money_count'] = count($r['channel_money']);
        $r['channel_short_count'] = count($r['channel_short']);
        $r['channel_other_count'] = count($r['channel_other']);

        // arrays finais
        $r['channel_urls_total'] = array_keys($r['channel_urls_total']);
        $r['channel_social'] = array_keys($r['channel_social']);
        $r['channel_money'] = array_keys($r['channel_money']);
        $r['channel_short'] = array_keys($r['channel_short']);
        $r['channel_other'] = array_keys($r['channel_other']);
    }
    unset($r);

    uasort($result, fn($a, $b) => $b['video_money_avg'] <=> $a['video_money_avg']);

    file_put_contents(
        storage_path('app/bench/external_monetization_urls_split.json'),
        json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    echo view('bench/debug-links-table', ['result' => $result]);
});


function classifyTextUrls(
    string $text,
    array $socialDomains,
    array $moneyDomains,
    array $shorteners,
    array $moneyTextPatterns
): array {
    preg_match_all(
        '~https?://[^\s<>"\']+|www\.[^\s<>"\']+~i',
        $text,
        $matches
    );

    $urls = $matches[0] ?? [];

    $out = [
        'total' => [],
        'social' => [],
        'money' => [],
        'short' => [],
        'other' => [],
        'money_text_hit' => 0,
    ];

    foreach ($urls as $url) {
        $url = normalizeUrl($url);

        $host = parse_url($url, PHP_URL_HOST);
        $host = strtolower($host ?? '');
        $host = preg_replace('/^www\./', '', $host);

        if (!$host) {
            continue;
        }

        $out['total'][$url] = true;

        if (containsDomain($host, $socialDomains)) {
            $out['social'][$url] = true;
        } elseif (containsDomain($host, $moneyDomains)) {
            $out['money'][$url] = true;
        } elseif (containsDomain($host, $shorteners)) {
            $out['short'][$url] = true;
        } else {
            $out['other'][$url] = true;
        }
    }

    foreach ($moneyTextPatterns as $pattern) {
        if (preg_match($pattern, $text)) {
            $out['money_text_hit'] = 1;
            break;
        }
    }

    foreach (['total', 'social', 'money', 'short', 'other'] as $key) {
        $out[$key] = array_keys($out[$key]);
        $out[$key . '_count'] = count($out[$key]);
    }

    return $out;
}

function normalizeUrl(string $url): string
{
    $url = trim($url);
    $url = rtrim($url, ".,);]}>\"'");

    if (!str_starts_with($url, 'http')) {
        $url = 'https://' . $url;
    }

    return $url;
}

function containsDomain(string $host, array $domains): bool
{
    foreach ($domains as $domain) {
        $domain = strtolower($domain);

        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
            return true;
        }
    }

    return false;
}




########################################### discriminacao dataset
Route::get('/ds', function () {

    $path = storage_path('app/bench/polarization_videos.json');
    $videos = json_decode(file_get_contents($path), true);

    $result = [];

    foreach ($videos as $v) {
        $ch = $v['channelId'] ?? 'sem_channel_id';

        if (!isset($result[$ch])) {
            $result[$ch] = [
                'handle' => $v['seed_handle'] ?? '',
                'dimensao' => $v['seed_dimension'] ?? '',
                'esperado' => $v['seed_expected_label'] ?? '',
                'total_videos_canal' => (int) ($v['channelVideos'] ?? 0),

                'num_videos' => 0,
                'view_sum' => 0,
                'like_sum' => 0,
                'comment_sum' => 0,
                'duration_sum' => 0,

                'title_len_sum' => 0,
                'desc_len_sum' => 0,
                'tags_count_sum' => 0,
            ];
        }

        $result[$ch]['num_videos']++;

        $result[$ch]['view_sum'] += (int) ($v['viewCount'] ?? 0);
        $result[$ch]['like_sum'] += (int) ($v['likeCount'] ?? 0);
        $result[$ch]['comment_sum'] += (int) ($v['commentCount'] ?? 0);
        $result[$ch]['duration_sum'] += (float) ($v['duration'] ?? 0);

        $result[$ch]['title_len_sum'] += mb_strlen($v['videoTitle'] ?? '', 'UTF-8');
        $result[$ch]['desc_len_sum'] += mb_strlen($v['videoDesc'] ?? '', 'UTF-8');
        $result[$ch]['tags_count_sum'] += is_array($v['videoTags'] ?? null)
            ? count($v['videoTags'])
            : 0;
    }

    foreach ($result as &$r) {
        $q = $r['num_videos'];
        $total = $r['total_videos_canal'];

        $r['pct_processado'] = $total > 0 ? ($q / $total) * 100 : 0;

        $r['views_avg'] = $q ? $r['view_sum'] / $q : 0;
        $r['likes_avg'] = $q ? $r['like_sum'] / $q : 0;
        $r['coment_avg'] = $q ? $r['comment_sum'] / $q : 0;
        $r['dur_avg'] = $q ? $r['duration_sum'] / $q : 0;

        $r['len_title_avg'] = $q ? $r['title_len_sum'] / $q : 0;
        $r['len_desc_avg'] = $q ? $r['desc_len_sum'] / $q : 0;
        $r['qtd_tags_avg'] = $q ? $r['tags_count_sum'] / $q : 0;
    }
    unset($r);

    // Ordena pela % de vídeos processados
    uasort($result, fn($a, $b) => $b['pct_processado'] <=> $a['pct_processado']);

    $pad = function ($text, $width, $align = STR_PAD_RIGHT) {
        $text = (string) $text;

        if (mb_strlen($text, 'UTF-8') > $width) {
            $text = mb_substr($text, 0, $width - 1, 'UTF-8') . '…';
        }

        $len = mb_strlen($text, 'UTF-8');
        $spaces = max(0, $width - $len);

        return $align === STR_PAD_LEFT
            ? str_repeat(' ', $spaces) . $text
            : $text . str_repeat(' ', $spaces);
    };

    $fmt = fn($n, $dec = 0) => number_format((float) $n, $dec, '.', ',');

    echo "<pre style='font-family: Consolas, Menlo, monospace; font-size:13px;'>";

    echo $pad("Rank", 5, STR_PAD_LEFT) .
         "  " . $pad("Handle", 24) .
         "  " . $pad("Dim.", 10) .
         "  " . $pad("Esperado", 12) .
         "  " . $pad("Proc.", 7, STR_PAD_LEFT) .
         "  " . $pad("Total", 9, STR_PAD_LEFT) .
         "  " . $pad("%", 8, STR_PAD_LEFT) .
         "  " . $pad("Views(avg)", 12, STR_PAD_LEFT) .
         "  " . $pad("Likes(avg)", 12, STR_PAD_LEFT) .
         "  " . $pad("Com(avg)", 10, STR_PAD_LEFT) .
         "  " . $pad("Dur(avg)", 9, STR_PAD_LEFT) .
         "  " . $pad("TitLen", 8, STR_PAD_LEFT) .
         "  " . $pad("DescLen", 8, STR_PAD_LEFT) .
         "  " . $pad("Tags", 6, STR_PAD_LEFT);

    echo "\n";
    echo str_repeat("-", 164) . "\n";

    $rank = 1;

    foreach ($result as $r) {
        echo $pad($rank, 5, STR_PAD_LEFT) .
             "  " . $pad($r['handle'], 24) .
             "  " . $pad($r['dimensao'], 10) .
             "  " . $pad($r['esperado'], 12) .
             "  " . $pad($fmt($r['num_videos']), 7, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['total_videos_canal']), 9, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['pct_processado'], 2) . '%', 8, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['views_avg']), 12, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['likes_avg']), 12, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['coment_avg']), 10, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['dur_avg'], 1), 9, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['len_title_avg'], 1), 8, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['len_desc_avg'], 1), 8, STR_PAD_LEFT) .
             "  " . $pad($fmt($r['qtd_tags_avg'], 1), 6, STR_PAD_LEFT);

        echo "\n";
        $rank++;
    }

    echo "\n\n";
    echo "Legenda: 1724 videos\n";
    echo "Proc.    = Num Videos processados no dataset\n";
    echo "Total    = Tot Videos do canal informado em channelVideos\n";
    echo "%        = Percentual do canal processado no dataset\n";
    echo "Com(avg) = Coment(avg)\n";
    echo "TitLen   = Len_Title(avg)\n";
    echo "DescLen  = Len_Desc(avg)\n";
    echo "Tags     = Qtd_Tags(avg)\n";

    echo "</pre>";
});




Route::get('/bench/polarization', function () {

    $path = 'bench/polarization_summary.json';

    if (!Storage::exists($path)) {
        return "Resumo não encontrado. Rode: php artisan bench:polarization-analyze";
    }

    $data = json_decode(Storage::get($path), true);

    return view('bench.polarization', compact('data'));

});

#######################################################################################
#######################################################################################


#Auth::loginUsingId(7);

Route::get('/', function () {
    return view('home');
})->name('home');



    Route::get('tarefa1', Tarefa1::class)->name('tarefa1');
    Route::get('tarefa2', Tarefa2::class)->name('tarefa2');
    Route::get('tarefa3', Tarefa3::class)->name('tarefa3');
    Route::get('tarefa4', Tarefa4::class)->name('tarefa4');
    Route::get('resultados', Resultados::class)->name('resultados');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


Route::get('polarizacao', Polarizacao::class)->name('polarizacao');
Route::get('toxicidade', Toxicidade::class)->name('toxicidade');
Route::get('monetizacao', Monetizacao::class)->name('monetizacao');
Route::get('tese', Tese::class)->name('tese');



Route::get('busca', Busca::class)->name('busca');
Route::get('video', Video::class)->name('video');
Route::get('canal', Canal::class)->name('canal');
Route::get('monet', Monet::class)->name('monet');


Route::get('arxiv/{canal_id?}', Arxiv::class)->name('arxiv');

Route::get('graf/{canal?}', Graf::class)->name('graf');
Route::get('toxic/{video?}', Toxic::class)->name('toxic');
Route::get('nlp/{busca?}', Nlp::class)->name('nlp');

Route::get('comentario/{video_id?}', Comentario::class)->name('comentario');



require __DIR__ . '/auth.php';
