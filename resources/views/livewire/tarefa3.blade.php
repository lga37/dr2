<div>


    {{-- WIDGET 3 | Cabeçalho informativo --}}
    <div class="bg-white border rounded-2xl p-6 md:p-7 shadow-sm mb-6">

        {{-- título --}}
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-indigo-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 7h16M4 12h10M4 17h13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </div>

            <div>
                <h2 class="text-xl md:text-2xl font-semibold">
                    WIDGET 3 — Nuvem de palavras <span class="text-indigo-700">(Wordcloud)</span>
                </h2>
                <p class="mt-1 text-slate-600 text-sm md:text-base max-w-7xl">
                    Visualize e compare os <strong>temas mais recorrentes</strong> de um ou dois canais do YouTube
                    a partir da frequência de palavras nos <strong>títulos dos vídeos</strong>.
                </p>
            </div>
        </div>

        {{-- boxes --}}
        <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">

            {{-- O que você faz --}}
            <div class="p-4 rounded-xl bg-slate-50 border">
                <h3 class="font-semibold mb-1">O que você faz</h3>
                <p class="text-slate-700">
                    Selecione canais por <strong>pesquisa</strong> ou via <strong>ID/URL</strong>
                    e clique em <strong>Avaliar canais</strong> para gerar a nuvem de palavras.
                </p>
            </div>

            {{-- O que analisamos --}}
            <div class="p-4 rounded-xl bg-slate-50 border">
                <h3 class="font-semibold mb-1">O que analisamos</h3>
                <p class="text-slate-700">
                    Extraímos e contabilizamos as palavras mais frequentes dos <strong>títulos dos vídeos</strong>,
                    removendo <em>stop words</em> e termos pouco informativos.
                </p>
            </div>

            {{-- Resultado --}}
            <div class="p-4 rounded-xl bg-slate-50 border">
                <h3 class="font-semibold mb-1">Resultado</h3>
                <p class="text-slate-700">
                    Uma <strong>nuvem de palavras</strong> em que o tamanho reflete a frequência,
                    facilitando a identificação rápida dos <strong>temas dominantes</strong>.
                </p>
            </div>

        </div>

        {{-- nota metodológica --}}
        <div class="mt-5 p-4 rounded-xl bg-sky-50 border border-sky-100 text-sky-900 text-sm">
            <span class="font-semibold">Nota metodológica:</span>
            a nuvem de palavras é uma forma visual de <strong>histograma textual</strong>,
            projetada para facilitar a interpretação humana de grandes volumes de títulos.
            A análise inicial considera apenas títulos, podendo ser estendida para descrições e palavras-chave.
        </div>

        {{-- feedback --}}
        <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-900 text-sm">
            <span class="font-semibold">Ao final:</span>
            solicitamos um <strong>feedback qualitativo</strong> sobre como o WIDGET auxilia
            a análise, exploração e sumarização de conteúdo — para pesquisadores e usuários em geral.
        </div>

    </div>


    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <x-selecionados-table :items="$selecionados" type="canal" remove="removeSelecionado"
                    clear="clearSelecionados" evaluate="validarTarefa3" :min="1" :max="1" />
                <x-search-add-bar variant="canal" query-model="query" on-search="pesquisarCanais" add-model="addInput"
                    on-add="addCanalByInput" />
                <x-results-table variant="canal" :items="$this->buscas" :selected="array_keys($selecionados ?? [])" />
            </div>


            {{-- B L O C O   D E   A V A L I A Ç Ã O --}}
            @if ($mostrarAvaliacao)
                <div id="avaliacao" class="mt-8 px-4">



                    @php
                        $cols = min(4, max(1, count($selecionados)));
                    @endphp
                    {{-- “Bleed” para ficar do tamanho da tabela abaixo --}}
                    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
                        <div class="grid gap-6 auto-rows-fr"
                            style="grid-template-columns: repeat({{ $cols }}, minmax(0,1fr));">
                            @foreach ($selecionados as $id => $v)
                                @php @endphp
                                <article wire:key="{{ $id }}"
                                    class="h-full flex flex-col rounded-xl border p-4 shadow-sm bg-white ring-2 ring-indigo-500">
                                    <x-cardcanal :v="$v" />
                                </article>
                            @endforeach
                        </div>
                    </div>


                    <div class="overflow-x-auto mt-8">
                        {{-- <table
                            class="divide-y divide-gray-200 divide-solid table-auto min-w-full text-sm tracking-tight leading-tight">
                            <thead>
                                <tr class="bg-gray-100 text-xs text-gray-700 text-center">
                                    @php
                                        $videosSessao = $videos_dos_canais;
                                        $numVids = max(count($videosSessao), 1); // evita /0
                                        $colWidth = number_format(100 / ($numVids * 7), 2);
                                    @endphp
                                    @foreach ($videosSessao as $canal_id => $dados)
                                        <th colspan="7" style="width: {{ $colWidth * 7 }}%;"
                                            class="border border-gray-300 px-2 py-4">
                                            <x-linkcanal :canalId="$canal_id" :titulo="$selecionados[$canal_id]['channelTitle'] ?? ''" />
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $max = collect($videosSessao)->map(fn($d) => count($d))->max() ?? 0;
                                @endphp

                                @for ($i = 0; $i < $max; $i++)
                                    @if ($i == 0)
                                        <tr
                                            class="border border-gray-300 w-[10px] font-bold text-center text-indigo-800 text-[10px] ">
                                            <td>#</td>
                                            <td>Titulo</td>
                                            <td>Likes</td>
                                            <td>Views</td>
                                            <td>Comments</td>
                                            <td>Data</td>
                                            <td>NLP</td>
                                        </tr>
                                    @endif

                                    <tr class="">
                                        @foreach ($videosSessao as $loopIndex => $dados)
                                            @php
                                                $c = $dados[$i] ?? null;
                                            @endphp
                                            @if ($c)
                                                <td
                                                    class="border border-gray-300 w-[10px] text-left text-gray-800 text-[10px] ">
                                                    {{ $i + 1 }}</td>
                                                <td
                                                    class="border text-xs border-gray-300 w-[420px] max-w-[420px] truncate break-all">
                                                    <a href="{{ $c['videoId'] }}" target="_blank">
                                                        {{ \Illuminate\Support\Str::limit(strip_tags($c['videoTitle'] ?? '[---]'), 120) }}
                                                    </a>
                                                </td>

                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ $c['videoLikeCount'] ?? '-' }}</td>
                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ $c['videoViewCount'] ?? '-' }}</td>
                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ $c['videoCommentCount'] ?? '-' }}</td>
                                                <td class="border border-gray-300 w-[20px] text-gray-800 text-[10px]">
                                                    {{ isset($c['videoDt']) ? \Carbon\Carbon::parse($c['videoDt'])->format('d/m/Y') : '--' }}
                                                </td>
                                                <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                                    {{ isset($c['nlp1']) ? number_format($c['nlp1'], 2) . '%' : 'X' }}
                                                </td>
                                            @else
                                                <td colspan="7"
                                                    class="border border-gray-300 w-full text-gray-900 text-center italic text-[11px]">
                                                    --</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>


                        </table> --}}

                        <!-- feedback -->
                        <div class="rounded-lg p-4 ring-4 w-full max-w-6xl mx-auto my-4 bg-green-50  ring-green-300">
                            <div class="mt-4 grid gap-2">
                                <label class="text-sm font-medium text-gray-700">
                                    Deixe um breve feedback: por que você escolheu esse canal?
                                </label>
                                <textarea rows="3" wire:model.defer="feedback"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex.: Pelo nome e engajamento (views, data de criação, tags, etc.) , achei que este canal seria mais 
                                    toxico, etc ..."></textarea>

                                <div class="flex items-center gap-3">
                                    <x-primary-button wire:click="salvarFeedback" wire:loading.attr="disabled"
                                        wire:target="salvarFeedback">
                                        Enviar feedback
                                    </x-primary-button>
                                    <div class="invisible" wire:loading.class.remove="invisible"
                                        wire:target="salvarFeedback">
                                        <span class="text-sm text-gray-500">Salvando…</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>




@php
    #$pm = session('pm_result', []);
    $pm = $this->pmResult;
@endphp

@if (!empty($pm))
    <div class="mx-auto max-w-12xl p-6">
        <h2 class="text-xl font-semibold mb-4">
            Widget P–M — Polarização e Monetização por buckets temporais
        </h2>

        @foreach ($pm as $channelId => $row)
            @php
                $isGreen = ($row['cor'] ?? '') === 'green';

                $border = $isGreen ? 'border-green-500' : 'border-red-500';
                $bg = $isGreen ? 'bg-green-50' : 'bg-red-50';
                $text = $isGreen ? 'text-green-800' : 'text-red-800';
            @endphp

            <div class="mb-8 rounded-2xl border-4 {{ $border }} {{ $bg }} p-5 shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="text-xs text-slate-500">ID: {{ $channelId }}</div>
                        <h3 class="text-lg font-bold {{ $text }}">
                            {{ $row['channel']['channelTitle'] ?? $channelId }}
                        </h3>
                    </div>

                    <div class="text-right text-sm">
                        <div><strong>VidIQ:</strong>
                            US$ {{ number_format($row['monetizacao_canal']['vidiq_monthly_avg_usd'] ?? 0, 0, ',', '.') }}/mês
                        </div>
                        <div><strong>URLs externas:</strong>
                            {{ $row['monetizacao_canal']['external_urls_count'] ?? 0 }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    @foreach ($row['buckets'] as $bucket)
                        @php
                            $a = $bucket['analysis'];
                            $p = $a['polarizacao'];
                            $m = $a['monetizacao_off_platform'];
                            $wc = $a['wordclouds'];
                        @endphp

                        <div class="rounded-xl bg-white border p-3 text-xs shadow-sm">
                            <div class="font-bold {{ $text }}">
                                Bucket {{ $bucket['idx'] }}
                            </div>

                            <div class="text-slate-500 mb-2">
                                {{ $bucket['label'] }}
                            </div>

                            <div><strong>Vídeos:</strong> {{ $a['videos_count'] }}</div>
                            <div><strong>Categoria:</strong> {{ $p['categoria_dominante'] }}</div>
                            <div><strong>Polo:</strong> {{ $p['polo_dominante'] }}</div>
                            <div><strong>Score P:</strong> {{ $p['score_medio'] ?? '-' }}</div>
                            <div><strong>Conf.:</strong> {{ $p['confianca_media'] ?? '-' }}</div>
                            <div><strong>URLs/vídeo:</strong> {{ $m['urls_media_por_video'] }}</div>

                            <div class="mt-3 border-t pt-2">
                                <strong>Títulos</strong>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach (array_slice($wc['titulos'], 0, 8, true) as $word => $freq)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100">
                                            {{ $word }} {{ $freq }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-2">
                                <strong>Descrições</strong>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach (array_slice($wc['descricoes'] ?? [], 0, 8, true) as $word => $freq)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100">
                                            {{ $word }} {{ $freq }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>      
                            
                            <div class="mt-2">
                                <strong>Transcrições</strong>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach (array_slice($wc['transcricoes'] ?? [], 0, 8, true) as $word => $freq)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100">
                                            {{ $word }} {{ $freq }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-2">
                                <strong>Tags</strong>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach (array_slice($wc['tags'], 0, 8, true) as $word => $freq)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100">
                                            {{ $word }} {{ $freq }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @php
                        $vidiqUrl = 'https://vidiq.com/youtube-stats/channel/' . $channelId . '/';
                    @endphp

                    <div
                        class="rounded-xl border-2 {{ $border }} p-3 text-xs shadow-sm relative overflow-hidden"
                        style="
                            background-image: repeating-linear-gradient(
                                135deg,
                                rgba(255,255,255,0.95) 0px,
                                rgba(255,255,255,0.95) 8px,
                                rgba(0,0,0,0.035) 8px,
                                rgba(0,0,0,0.035) 16px
                            );
                        "
                    >
                        <div class="absolute top-0 left-0 right-0 h-1 {{ $isGreen ? 'bg-green-500' : 'bg-red-500' }}"></div>

                        <div class="font-bold {{ $text }} mb-3">
                            Monetização
                        </div>

                        <div class="mt-2">
                            <strong>VidIQ/mês:</strong><br>
                            US$ {{ number_format($row['monetizacao_canal']['vidiq_monthly_avg_usd'] ?? 0, 0, ',', '.') }}
                        </div>

                        <div class="mt-2">
                            <strong>Off-platform:</strong><br>
                            {{ $row['monetizacao_canal']['external_urls_count'] ?? 0 }} URLs
                        </div>

                        <div class="mt-3 pt-3 border-t border-slate-200">
                            <a
                                href="{{ $vidiqUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md {{ $isGreen ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }} font-semibold"
                            >
                                Conferir no VidIQ ↗
                            </a>
                        </div>
                    </div>




                </div>
            </div>
        @endforeach
    </div>
@endif




                <h2 class="text-xl font-semibold mt-8 mb-3">Nuvem de palavras</h2>
                <div class="w-full">
                    <div class="grid w-full grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
                        @foreach ($selecionados as $canalId => $raw)
                            @php
                                $items = $word_ranking[$canalId] ?? [];
                            @endphp

                            <div class="w-full min-w-0 rounded border bg-white/60 p-3 flex flex-col">
                                <div class="text-sm font-semibold leading-tight">
                                    {{ $raw['channelTitle'] ?? $canalId }}
                                </div>
                                <div class="text-[11px] text-gray-500 mb-2">
                                    Top termos (títulos)
                                </div>


                                <div class="wcTextCloud">
                                    @foreach ($cloudTokens[$canalId] ?? [] as $t)
                                        <span class="wcWord"
                                            style="font-size: {{ $t['size'] }}px; color: {{ $t['color'] }}; opacity: {{ $t['alpha'] }};"
                                            title="{{ $t['word'] }} • {{ $t['count'] }}">{{ $t['word'] }}</span>
                                    @endforeach
                                </div>



                                {{-- ranking --}}
                                <div class="mt-2 pt-2 border-t w-full">
                                    <div class="text-[11px] text-gray-500 mb-1">
                                        Ranking (Top {{ min(30, count($items)) }}) — decrescente
                                    </div>

                                    <div class="flex flex-wrap gap-1.5 w-full">
                                        @foreach (array_slice($items, 0, 30) as $row)
                                            @php
                                                $pal = [
                                                    [
                                                        'bg' => 'bg-red-50',
                                                        'bd' => 'border-red-200',
                                                        'tx' => 'text-red-700',
                                                    ],
                                                    [
                                                        'bg' => 'bg-amber-50',
                                                        'bd' => 'border-amber-200',
                                                        'tx' => 'text-amber-700',
                                                    ],
                                                    [
                                                        'bg' => 'bg-lime-50',
                                                        'bd' => 'border-lime-200',
                                                        'tx' => 'text-lime-700',
                                                    ],
                                                    [
                                                        'bg' => 'bg-emerald-50',
                                                        'bd' => 'border-emerald-200',
                                                        'tx' => 'text-emerald-700',
                                                    ],
                                                    [
                                                        'bg' => 'bg-sky-50',
                                                        'bd' => 'border-sky-200',
                                                        'tx' => 'text-sky-700',
                                                    ],
                                                    [
                                                        'bg' => 'bg-indigo-50',
                                                        'bd' => 'border-indigo-200',
                                                        'tx' => 'text-indigo-700',
                                                    ],
                                                    [
                                                        'bg' => 'bg-fuchsia-50',
                                                        'bd' => 'border-fuchsia-200',
                                                        'tx' => 'text-fuchsia-700',
                                                    ],
                                                ];
                                                $idx = crc32($row['word']) % count($pal);
                                                $c = $pal[$idx];
                                            @endphp

                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] leading-none {{ $c['bg'] }} {{ $c['bd'] }} {{ $c['tx'] }}">
                                                <span class="font-medium">{{ $row['word'] }}</span>
                                                <span class="opacity-50">•</span>
                                                <span class="font-semibold tabular-nums">{{ $row['count'] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


            @endif


        </div>
    </div>
</div>
