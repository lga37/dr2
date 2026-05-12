<div>


    {{-- WIDGET 3 | Cabeçalho informativo --}}
    <div class="bg-white border rounded-2xl p-6 md:p-7 shadow-sm mb-6">

        {{-- título --}}
        <div class="flex items-start gap-4">
            <div class="w-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-indigo-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 7h16M4 12h10M4 17h13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-semibold">
                    WIDGET 3 — Polarização e <span class="text-indigo-700">Monetizacao</span>
                </h2>
            </div>
        </div>

        {{-- bloco resumido --}}
        <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- resumo --}}
            <div
                class="lg:col-span-2 rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm">

                <h3 class="text-base font-semibold text-indigo-900 mb-2">
                    Como utilizar
                </h3>

                <div class="text-sm text-slate-700 leading-6 space-y-2">

                    <p>
                        Pesquise canais relacionados ao tema desejado, adicione pelo menos
                        <strong>2 canais</strong> na sessão e clique em
                        <strong>“AVALIAR CANAIS”</strong>.
                    </p>

                    <p>
                        O sistema divide os vídeos em períodos temporais (“buckets”)
                        e analisa títulos, descrições, tags, transcrições,
                        polarização ideológica e possíveis indicadores de monetização.
                    </p>

                    <p>
                        As nuvens de palavras destacam os termos mais frequentes:
                        quanto maior a palavra, maior sua relevância estatística.
                    </p>

                </div>

            </div>

            {{-- legenda --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h3 class="text-base font-semibold text-slate-900 mb-3">
                    Legenda
                </h3>

                <div class="space-y-2 text-sm">

                    <div class="flex items-center justify-between border-b pb-1">
                        <span class="text-slate-600">Score P</span>
                        <span class="font-medium text-indigo-700">
                            Intensidade da polarização
                        </span>
                    </div>

                    <div class="flex items-center justify-between border-b pb-1">
                        <span class="text-slate-600">Conf.</span>
                        <span class="font-medium text-emerald-700">
                            Confiança da IA
                        </span>
                    </div>

                    <div class="flex items-center justify-between border-b pb-1">
                        <span class="text-slate-600">URLs/vídeo</span>
                        <span class="font-medium text-amber-700">
                            Links externos (proxy monetização off)
                        </span>
                    </div>

                    <div class="flex items-center justify-between border-b pb-1">
                        <span class="text-slate-600">VidIQ/mês</span>
                        <span class="font-medium text-rose-700">
                            Estimativa monetização on
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Buckets</span>
                        <span class="font-medium text-sky-700">
                            Períodos temporais
                        </span>
                    </div>

                </div>

            </div>

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

                            <div
                                class="mb-8 rounded-2xl border-4 {{ $border }} {{ $bg }} p-5 shadow-sm">
                                <div class="flex justify-between items-start mb-4">
                                    <div>

                                        <x-linkcanal :canalId="$channelId" :titulo="$row['channel']['channelTitle'] ?? '—'" />

                                    </div>

                                    <div class="text-right text-sm">


                                        <a href="https://vidiq.com/youtube-stats/channel/{{ $channelId }}/"
                                            target="_blank" class="text-sm text-blue-600 underline">
                                            VidIQ:

                                            @if (!empty($row['monetizacao_canal']['vidiq_monthly_avg_usd']))
                                                US$
                                                {{ number_format($row['monetizacao_canal']['vidiq_monthly_avg_usd'], 0, ',', '.') }}/mês
                                            @else
                                                sem dados
                                            @endif
                                        </a>

                                        <div><strong>URLs externas:</strong>

                                            {{ $row['urls_total'] ?? 0 }}
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

                                    <div class="rounded-xl border-2 {{ $border }} p-3 text-xs shadow-sm relative overflow-hidden"
                                        style="
                            background-image: repeating-linear-gradient(
                                135deg,
                                rgba(255,255,255,0.95) 0px,
                                rgba(255,255,255,0.95) 8px,
                                rgba(0,0,0,0.035) 8px,
                                rgba(0,0,0,0.035) 16px
                            );
                        ">
                                        <div
                                            class="absolute top-0 left-0 right-0 h-1 {{ $isGreen ? 'bg-green-500' : 'bg-red-500' }}">
                                        </div>

                                        <div class="font-bold {{ $text }} mb-3">
                                            Monetização
                                        </div>

                                        <div class="mt-2">
                                            <strong>VidIQ/mês:</strong><br>
                                            US$
                                            {{ number_format($row['monetizacao_canal']['vidiq_monthly_avg_usd'] ?? 0, 0, ',', '.') }}
                                        </div>

                                        <div class="mt-2">
                                            <strong>Off-platform:</strong><br>

                                            {{ $row['urls_total'] ?? 0 }}
                                            URLs

                                            @if (!empty($row['urls']))
                                                <div class="text-xs mt-2">
                                                    @foreach (array_slice($row['urls'], 0, 3) as $url)
                                                        <div class="truncate">{{ $url }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <a href="{{ $vidiqUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md {{ $isGreen ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }} font-semibold">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($pm as $channelId => $row)
                        @php
                            $grupos = [
                                'Títulos' => 'titulos',
                                'Descrições' => 'descricoes',
                                'Tags' => 'tags',
                            ];

                            $clouds = [];

                            foreach ($grupos as $label => $key) {
                                $freq = [];

                                foreach ($row['buckets'] ?? [] as $bucket) {
                                    $wc = $bucket['analysis']['wordclouds'][$key] ?? [];

                                    foreach ($wc as $word => $count) {
                                        $freq[$word] = ($freq[$word] ?? 0) + (int) $count;
                                    }
                                }

                                arsort($freq);
                                $clouds[$label] = $freq;
                            }
                        @endphp

                        <div class="rounded border bg-white p-4">
                            <div class="font-bold mb-1">
                                {{ $row['channel']['channelTitle'] ?? $channelId }}
                            </div>

                            @foreach ($clouds as $label => $freq)
                                <div class="mt-4">
                                    <div class="text-xs font-semibold text-gray-500 mb-2">
                                        {{ $label }}
                                    </div>

                                    <div class="flex flex-wrap gap-2 items-center">
                                        @forelse(array_slice($freq, 0, 30, true) as $word => $count)
                                            @php
                                                $i = $loop->index;
                                                $size = match (true) {
                                                    $i === 0 => 30,
                                                    $i === 1 => 25,
                                                    $i === 2 => 21,
                                                    $i === 3 => 18,
                                                    $i === 4 => 16,
                                                    default => 13,
                                                };
                                            @endphp

                                            <span class="font-semibold text-slate-700"
                                                style="font-size: {{ $size }}px"
                                                title="{{ $word }}: {{ $count }}">
                                                {{ $word }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400">Sem termos.</span>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

            @endif


        </div>
    </div>
</div>
