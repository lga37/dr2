<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tarefa2 - Analise temporal de sentimento do conteudo gerado pelo usuario (UGC)') }}
        </h2>
        <p>
            A Tarefa 2 tem como objetivo analisar a evolução do sentimento associado aos vídeos.
            Para isso, todo o acervo de vídeos públicos dos canais selecionados é coletado e, para cada item, aplica-se
            uma
            análise de linguagem natural (NLP) no título e na descrição [-1;+1]. Permitindo quantificar a polaridade
            discursiva
            do conteúdo. O resultado possibilita observar tendências temporais de polaridade, identificar momentos de
            maior ou
            menor positividade/negatividade no discurso e, potencialmente, correlacionar essas variações com eventos,
            mudanças editoriais ou temas abordados pelo canal.
        </p>
    </x-slot>
    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">

                @if (count($this->sessaoCanais))
                    <div class="mt-6 mb-6">
                        <h3 class="font-semibold mb-2">Na sessão : ({{ count($selecionados) }})</h3>
                        <table class="min-w-full text-sm border divide-y divide-gray-300 table-auto">
                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @foreach ($this->sessaoCanais as $c)
                                    <tr>
                                        <td class="px-2 py-1">
                                            <button class="text-red-600"
                                                wire:click="removeSelecionado('{{ $c['canalId'] }}')">
                                                remover
                                            </button>
                                        </td>
                                        <td class="px-2 py-1">{{ $c['canalId'] }}</td>
                                        <td class="px-2 py-1">
                                            <a href="https://www.youtube.com/channel/{{ $c['canalId'] }}"
                                                target="_blank"
                                                class="text-blue-600 font-semibold">{{ $c['title'] }}</a>
                                        </td>
                                        <td class="px-2 py-1">{{ $c['pais'] }}</td>
                                        <td class="px-2 py-1">{{ number_format($c['inscritos'] ?? 0) }}</td>
                                        <td class="px-2 py-1">{{ number_format($c['videos'] ?? 0) }}</td>
                                        <td class="px-2 py-1">{{ number_format($c['views'] ?? 0) }}</td>
                                        <td class="px-2 py-1">
                                            {{ $c['published'] ? \Carbon\Carbon::parse($c['published'])->format('d/m/Y') : '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-2 flex items-center gap-3 text-sm">
                            @if (!empty($selecionados))
                                <x-danger-button wire:click="clearSelecionados">Limpar sessão</x-danger-button>
                            @endif

                            <x-primary-button wire:click="buscarVideos" wire:loading.attr="disabled">
                                Buscar Vídeos dos Selecionados
                            </x-primary-button>
                            <div wire:loading wire:target="buscarVideos" class="text-sm text-gray-500 mt-2">
                                Carregando resultados...
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center mb-6 min-w-full align-middle">
                    <x-text-input id="busca" placeholder="Tema (para achar canais pelos vídeos)" autocomplete="off"
                        wire:model.lazy="query" class="mt-1 w-96" />
                    <x-primary-button class="ms-3" type="button" onclick="document.getElementById('busca').focus()">
                        {{ __('Pesquisar') }}
                    </x-primary-button>

                    <span class="mx-4">OU</span>

                    <x-text-input id="addId" class="border rounded mt-1 w-96"
                        placeholder="Colar @handle, URL ou ID UC..." wire:model.lazy="addInput" />
                    <x-primary-button class="ms-3" type="button" wire:click="addCanalByInput">
                        Adicionar por ID/URL
                    </x-primary-button>
                </div>

                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">

                    @if (!empty($buscas))
                        <table class="min-w-full text-sm border divide-y divide-gray-300 table-auto">
                            <thead class="bg-gray-100 text-xs text-gray-600 text-center">
                                <tr>
                                    <th class="px-2 py-1 border">-</th>
                                    <th class="px-2 py-1 border">Id</th>
                                    <th class="px-2 py-1 border">Canal</th>
                                    <th class="px-2 py-1 border">País</th>
                                    <th class="px-2 py-1 border">Inscritos</th>
                                    <th class="px-2 py-1 border">Vídeos</th>
                                    <th class="px-2 py-1 border">Views</th>
                                    <th class="px-2 py-1 border">Desde</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @foreach ($buscas as $canal)
                                    <tr class="hover:bg-gray-50">

                                        <td class="border px-2 py-1">
                                            @php $cid = $canal['canalId']; @endphp
                                            @if (in_array($cid, $selecionados ?? []))
                                                <span class="text-green-700 text-xs font-semibold">✓</span>
                                            @else
                                                <input type="checkbox" x-data
                                                    @click.prevent="$wire.add('{{ $cid }}')">
                                            @endif
                                        </td>

                                        <td class="border px-2 py-1 text-left">{{ $canal['canalId'] ?? '' }}</td>

                                        <td class="border px-2 py-1 text-left">
                                            <a href="https://www.youtube.com/channel/{{ $canal['canalId'] }}"
                                                target="_blank" class="text-blue-600 hover:underline">
                                                {{ $canal['title'] }}
                                            </a>
                                        </td>
                                        <td class="border px-2 py-1">{{ $canal['pais'] ?? '-' }}</td>
                                        <td class="border px-2 py-1">{{ number_format($canal['inscritos'] ?? 0) }}</td>
                                        <td class="border px-2 py-1">{{ number_format($canal['videos'] ?? 0) }}</td>
                                        <td class="border px-2 py-1">{{ number_format($canal['views'] ?? 0) }}</td>
                                        <td class="border px-2 py-1 text-[11px]">
                                            {{ \Carbon\Carbon::parse($canal['published'])->format('d/m/Y') }}
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    @endif


                </div>

            </div>


            <!-- tabela dos videos -->
            <div class="overflow-x-auto mt-8">
                <table class="table-auto border border-gray-300 w-full text-[11px] leading-tight">
                    <thead>
                        <tr class="bg-gray-100 text-gray-800 text-center">
                            @foreach ($canais as $canal_id => $dados)
                                @php
                                    $numCanais = count($canais);
                                    $colWidth = number_format(100 / ($numCanais * 10), 2); // 7 colunas por canal agora
                                @endphp
                                <th colspan="10" style="width: {{ $colWidth * 10 }}%;"
                                    class="border border-gray-300 px-2 py-1 font-semibold">
                                    <a href="https://www.youtube.com/channel/{{ $canal_id }}" target="_blank"
                                        class="text-blue-600 hover:underline">
                                        {{ $canal_id }}
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-200 text-gray-700 text-center">
                            @foreach ($canais as $canal_id => $dados)
                                <th class="border border-gray-300 px-1">#</th>
                                <th class="border border-gray-300 px-1">Título</th>
                                <th class="border border-gray-300 px-1">Views</th>
                                <th class="border border-gray-300 px-1">Lik</th>
                                <th class="border border-gray-300 px-1">Com</th>
                                <th class="border border-gray-300 px-1">Dur</th>
                                <th class="border border-gray-300 px-1">Dt</th>
                                <th class="border border-gray-300 px-1">NLP tit</th>
                                <th class="border border-gray-300 px-1">NLP desc</th>
                                <th class="border border-gray-300 px-1">NLP avg</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $max = collect($canais)->map(fn($d) => count($d))->max() ?? 0;
                        @endphp

                        @for ($i = 0; $i < $max; $i++)
                            <tr class="text-center text-sm">
                                @foreach ($canais as $canalId => $videos)
                                    @php
                                        $v = $videos[$i] ?? null;
                                    @endphp

                                    @if ($v)
                                        <td class="border border-gray-300 px-1 text-gray-800">{{ $i + 1 }}</td>
                                        <td class="border border-gray-300 text-left px-1 text-blue-600 hover:underline">
                                            <a href="https://www.youtube.com/watch?v={{ $v['videoId'] }}"
                                                target="_blank">
                                                {{ \Illuminate\Support\Str::words($v['title'] ?? '[sem título]', 6, '...') }}
                                            </a>
                                        </td>
                                        <td class="border border-gray-300 px-1">
                                            {{ number_format($v['viewCount'] ?? 0) }}</td>
                                        <td class="border border-gray-300 px-1">
                                            {{ number_format($v['likeCount'] ?? 0) }}</td>
                                        <td class="border border-gray-300 px-1">
                                            {{ number_format($v['commentCount'] ?? 0) }}</td>
                                        <td class="border border-gray-300 px-1">
                                            @php
                                                $dur = $v['duration'] ?? 'PT0M0S';
                                                preg_match('/PT(\d+H)?(\d+M)?(\d+S)?/', $dur, $parts);
                                                $min = isset($parts[2]) ? intval($parts[2]) : 0;
                                                $sec = isset($parts[3]) ? intval($parts[3]) : 0;
                                            @endphp
                                            {{ str_pad($min, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($sec, 2, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="border border-gray-300 px-1">
                                            {{ isset($v['publishedAt']) ? \Carbon\Carbon::parse($v['publishedAt'])->format('m/Y') : '--/----' }}
                                        </td>


                                        <td class="border border-gray-300 px-1">{{ $v['nlp_title'] ?? 'n/a' }}</td>
                                        <td class="border border-gray-300 px-1">{{ $v['nlp_desc'] ?? 'n/a' }}</td>
                                        <td class="border border-gray-300 px-1">{{ $v['nlp_mean'] ?? 'n/a' }}</td>
                                    @else
                                        <td colspan="10" class="border border-gray-300 text-gray-400 italic">--</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>


            @php
                use Carbon\Carbon;

                // Converte NLP -1..+1 -> 0..1
                $to01 = fn($v) => is_null($v) ? null : max(0, min(1, ($v + 1) / 2));

                // Estilos fixos para cada série (cheia, tracejada, pontilhada)
                $lineStyles = [
                    '', // cheia
                    '6 3', // tracejada
                    '2 2', // pontilhada
                ];
            @endphp

            @foreach ($canais as $canal_id => $videos)
                @php
                    $col = collect($videos)->filter(fn($v) => !empty($v['publishedAt']));

                    if ($col->isEmpty()) {
                        $months = [];
                        $series = [];
                    } else {
                        $minDt = $col->min(fn($v) => Carbon::parse($v['publishedAt'])->startOfMonth());
                        $maxDt = $col->max(fn($v) => Carbon::parse($v['publishedAt'])->startOfMonth());

                        // Eixo X contínuo mês a mês
                        $months = [];
                        for ($dt = $minDt->copy(); $dt <= $maxDt; $dt->addMonth()) {
                            $months[] = $dt->format('m/Y');
                        }

                        // Agrupa por m/Y para lookup rápido
                        $byMonth = $col->groupBy(fn($v) => Carbon::parse($v['publishedAt'])->format('m/Y'));

                        // Função para extrair valores alinhados aos meses
                        $vals = function (string $key) use ($months, $byMonth) {
                            $out = [];
                            foreach ($months as $m) {
                                $g = $byMonth->get($m);
                                $out[] = $g ? (float) $g->avg(fn($v) => $v[$key] ?? null) : null;
                            }
                            return $out;
                        };

                        $series = [
                            'NLP média (−1..+1)' => array_map($to01, $vals('nlp_mean')),
                            'NLP título (−1..+1)' => array_map($to01, $vals('nlp_title')),
                            'NLP descrição (−1..+1)' => array_map($to01, $vals('nlp_desc')),
                        ];
                    }

                    // -------- SVG params
                    $W = 1000;
                    $H = 320;
                    $padL = 70;
                    $padR = 20;
                    $padT = 18;
                    $padB = 56;
                    $plotW = $W - $padL - $padR;
                    $plotH = $H - $padT - $padB;
                    $n = max(count($months) - 1, 1);
                    $tickStep = max(1, (int) ceil(count($months) / 12));

                    // Função para converter (i, valor) em coordenadas
                    $pt = function ($i, $y) use ($n, $plotW, $plotH, $padL, $padT) {
                        $x = $padL + $i * ($plotW / $n);
                        $yy = $padT + $plotH * (1 - $y); // y em 0..1
                        return [$x, $yy];
                    };
                @endphp

                <div class="mt-10 mb-10">
                    <div class="mb-2 text-sm">
                        <a class="text-blue-600 hover:underline"
                            href="https://www.youtube.com/channel/{{ $canal_id }}"
                            target="_blank">{{ $canal_id }}</a>
                    </div>

                    @if (!empty($months))
                        <div class="w-full overflow-x-auto">
                            <svg viewBox="0 0 {{ $W }} {{ $H }}" width="100%"
                                height="auto" role="img">
                                <rect x="0" y="0" width="{{ $W }}" height="{{ $H }}"
                                    fill="white" />
                                <rect x="{{ $padL }}" y="{{ $padT }}" width="{{ $plotW }}"
                                    height="{{ $plotH }}" fill="none" stroke="#ddd" />

                                

                                {{-- grid Y -100%..+100% --}}
                                @php $yTicks = [-1, -0.5, 0, 0.5, 1]; @endphp
                                @foreach ($yTicks as $yt)
                                    @php
                                        // normaliza -1..+1 -> 0..1 para posição no gráfico
                                        $yn = ($yt + 1) / 2;
                                        $gy = $padT + $plotH * (1 - $yn);
                                        // linha zero mais forte
                                        $stroke = $yt === 0 ? '#bbb' : '#eee';
                                        $sw = $yt === 0 ? 1.5 : 1;
                                        // label em porcentagem com sinal em positivos
                                        $label = ($yt > 0 ? '+' : ($yt < 0 ? '' : '')) . (int) ($yt * 100) . '%';
                                    @endphp
                                    <line x1="{{ $padL }}" y1="{{ $gy }}"
                                        x2="{{ $padL + $plotW }}" y2="{{ $gy }}"
                                        stroke="{{ $stroke }}" stroke-width="{{ $sw }}" />
                                    <text x="{{ $padL - 10 }}" y="{{ $gy + 4 }}" font-size="11"
                                        text-anchor="end">{{ $label }}</text>
                                @endforeach


                                {{-- eixo X contínuo (amostra ~12 labels) --}}
                                <line x1="{{ $padL }}" y1="{{ $padT + $plotH }}" x2="{{ $padL + $plotW }}"
                                    y2="{{ $padT + $plotH }}" stroke="#bbb" />
                                @foreach ($months as $i => $lab)
                                    @if ($i % $tickStep === 0)
                                        @php [$x,$y0]=$pt($i,0); @endphp
                                        <line x1="{{ $x }}" y1="{{ $padT + $plotH }}"
                                            x2="{{ $x }}" y2="{{ $padT + $plotH + 6 }}"
                                            stroke="#bbb" />
                                        <text x="{{ $x }}" y="{{ $padT + $plotH + 20 }}" font-size="11"
                                            text-anchor="middle">{{ $lab }}</text>
                                    @endif
                                @endforeach

                                {{-- séries --}}
                                @foreach ($series as $nome => $vals)
                                    @php
                                        $dash = $lineStyles[$loop->index % count($lineStyles)];
                                        $points = [];
                                        foreach ($vals as $i => $v) {
                                            if ($v !== null) {
                                                [$x, $y] = $pt($i, $v);
                                                $points[] = "$x,$y";
                                            }
                                        }
                                    @endphp
                                    <polyline fill="none" stroke-width="2" stroke="currentColor"
                                        stroke-dasharray="{{ $dash }}"
                                        points="{{ implode(' ', $points) }}" />
                                    @foreach ($vals as $i => $v)
                                        @if ($v !== null)
                                            @php [$cx,$cy] = $pt($i, $v); @endphp
                                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="2.5"
                                                fill="currentColor" />
                                        @endif
                                    @endforeach
                                @endforeach

                                {{-- legenda --}}
                                @php
                                    $lx = $padL;
                                    $ly = 16;
                                @endphp
                                @foreach (array_keys($series) as $idx => $nome)
                                    <line x1="{{ $lx }}" y1="{{ $ly - 4 }}"
                                        x2="{{ $lx + 22 }}" y2="{{ $ly - 4 }}" stroke="currentColor"
                                        stroke-width="2"
                                        stroke-dasharray="{{ $lineStyles[$idx % count($lineStyles)] }}" />
                                    <text x="{{ $lx + 28 }}" y="{{ $ly - 1 }}"
                                        font-size="12">{{ $nome }}</text>
                                    @php $ly+=16; @endphp
                                @endforeach

                                {{-- <text x="{{ $padL - 45 }}" y="{{ $padT + 12 }}" font-size="12">0–1</text> --}}
                                <text x="{{ $padL - 55 }}" y="{{ $padT + 12 }}" font-size="12">−100% a +100%</text>

                                <text x="{{ $padL + $plotW / 2 }}" y="{{ $H - 10 }}" font-size="12"
                                    text-anchor="middle">Meses</text>
                            </svg>
                        </div>
                    @else
                        <div class="text-sm text-gray-500">Sem dados deste canal.</div>
                    @endif
                </div>
            @endforeach


        </div>
    </div>
</div>
