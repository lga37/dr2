<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tarefa1 - Toxicidade de audiencia : nao inclui a trilha inteira de comentarios, somente o raiz.') }}
        </h2>
        <p>A Tarefa 1 tem como foco principal a análise comparativa da toxicidade nos comentários raiz de vídeos do
            YouTube.
            O exercício consiste em buscar vídeos relacionados a termos potencialmente polarizadores — como "aborto",
            por exemplo
            — e, a partir da seleção de vídeos exibidos, extrair seus comentários públicos e aplicar uma análise
            automática de
            toxicidade (API Perspective). O objetivo é comparar a média de toxicidade entre os vídeos retornados pela
            mesma busca,
            identificando variações discursivas entre canais, títulos, datas ou contextos narrativos distintos.
        </p>
    </x-slot>

    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <!-- Tabela dos vídeos na sessão -->
                @if (count($this->sessaoVideos))
                    <div class="mt-6">
                        <h3 class="font-semibold mb-2">Na sessão : ({{ count($selecionados) }})</h3>
                        <table class="min-w-full text-sm border divide-y divide-gray-300 table-auto">

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @foreach ($this->sessaoVideos as $v)
                                    <tr class="">
                                        <td class="px-2 py-1">
                                            <button class="text-red-600"
                                                wire:click="removeSelecionado('{{ $v['videoId'] }}')">remover</button>
                                        </td>
                                        <td class="px-2 py-1">{{ $v['videoId'] }}</td>
                                        <td class="px-2 py-1">
                                            <a href="https://www.youtube.com/watch?v={{ $v['videoId'] }}"
                                                target="_blank"
                                                class="text-blue-600 font-semibold">{{ $v['title'] }}</a>
                                        </td>
                                        <td class="px-2 py-1">{{ $v['channel'] }}</td>

                                        <td class="px-2 py-1">{{ $v['comments'] }}</td>
                                        <td class="px-2 py-1">
                                            {{ $v['published'] ? \Carbon\Carbon::parse($v['published'])->format('d/m/Y') : '' }}
                                        </td>


                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 flex items-center gap-3 text-sm">
                        @if (!empty($selecionados))
                            <x-danger-button wire:click="clearSelecionados" class="">
                                Limpar sessão
                            </x-danger-button>
                        @endif

                        <x-primary-button wire:click="buscarComentarios" wire:loading.attr="disabled">
                            Buscar Comentários dos Selecionados
                        </x-primary-button>
                        <div wire:loading wire:target="buscarComentarios" class="text-sm text-gray-500 mt-2">
                            Carregando resultados...
                        </div>
                    </div>
                @endif

                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">
                    <div class="flex items-center mb-6 min-w-full align-middle">
                        <x-text-input id="busca" placeholder="Tema ou Video especifico" autocomplete="off"
                            wire:model.lazy="query" class="mt-1 w-96" />
                        <x-primary-button class="ms-3" type="button"
                            onclick="document.getElementById('busca').focus()">
                            {{ __('Pesquisar') }}
                        </x-primary-button>
                        @error('query')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <x-input-error :messages="$errors->get('busca')" class="mt-2" />

                        <span class="mx-4">OU</span>

                        <x-text-input id="addId" class="border rounded mt-1 w-96"
                            placeholder="Colar URL ou ID do vídeo" wire:model.lazy="addInput" />
                        <x-primary-button class="ms-3" type="button" wire:click="addVideoByInput">
                            Adicionar por ID/URL
                        </x-primary-button>
                    </div>

                    <table class="min-w-full text-sm border divide-y divide-gray-300 table-auto">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left bg-gray-50">-</th>
                                <th class="px-6 py-3 text-left bg-gray-50">ID</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Título</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Canal</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Views</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Likes</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Comments</th>
                                <th class="px-6 py-3 text-left bg-gray-50">IR</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Duration</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Data</th>
                                <th class="px-6 py-3 text-left bg-gray-50">Thumb</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                            @forelse($this->buscas as $busca)
                                <tr class="">
                                    <td class="px-2 py-1">
                                        @php $vid = $busca['videoId']; @endphp

                                        @if (in_array($vid, $selecionados ?? []))
                                            <span
                                                class="inline-flex items-center gap-1 text-green-700 text-xs font-semibold">
                                                ✓
                                            </span>
                                        @else
                                            <!-- checkbox que só adiciona e não alterna visualmente -->
                                            <input type="checkbox" x-data
                                                @click.prevent="$wire.add('{{ $vid }}')">
                                            <!-- @click.prevent evita o 'check' local; Livewire re-renderiza e o input some -->
                                        @endif
                                    </td>


                                    <td class="px-2 py-1 text-sm">{{ $busca['videoId'] }}</td>
                                    <td class="px-2 py-1 text-sm">
                                        <a href="https://www.youtube.com/watch?v={{ $busca['videoId'] }}"
                                            class="text-blue-600 font-semibold" target="_blank">
                                            {{ $busca['title'] }}
                                        </a>
                                    </td>
                                    <td class="px-2 py-1 text-left text-sm">{{ $busca['channel'] }}</td>
                                    <td class="px-2 py-1 text-left text-sm">{{ $busca['views'] }}</td>
                                    <td class="px-2 py-1 text-left text-sm">{{ $busca['likes'] }}</td>
                                    <td class="px-2 py-1 text-left text-sm">{{ $busca['comments'] }}</td>



                                    <td class="px-2 py-1 text-sm">
                                        @php
                                            $vid = $busca['videoId'];
                                            $sel = in_array($vid, $selecionados ?? []);
                                            $ir = $this->ir[$vid] ?? null;
                                        @endphp

                                        @if ($sel)
                                            {{ !is_null($ir) ? number_format($ir, 2) : '…' }}
                                        @else
                                            —
                                        @endif
                                    </td>



                                    <td class="px-2 py-1 text-left text-sm">{{ $busca['duration'] }}</td>
                                    <td class="px-2 py-1 text-sm">
                                        {{ \Carbon\Carbon::parse($busca['published'])->format('d/m/Y') }}</td>
                                    <td class="px-2 py-1">
                                        <img src="{{ $busca['thumbnail'] }}" alt="thumb" class="w-20">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-sm text-gray-500">Nenhum resultado
                                        encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>


            <!-- tabela dos comentarios -->
            <div class="overflow-x-auto mt-8">
                <table
                    class="divide-y divide-gray-200 divide-solid table-auto min-w-full text-sm tracking-tight leading-tight">
                    <thead>
                        <tr class="bg-gray-100 text-xs text-gray-700 text-center">
                            @foreach ($comentarios as $video_id => $dados)
                                @php
                                    $numVideos = count($comentarios);
                                    $colWidth = number_format(100 / ($numVideos * 5), 2);
                                @endphp
                                <th colspan="5" style="width: {{ $colWidth * 5 }}%;"
                                    class="border border-gray-300 px-2 py-1">
                                    <a href="https://www.youtube.com/watch?v={{ $video_id }}" target="_blank"
                                        class="text-blue-600 hover:underline">
                                        {{ $video_id }}
                                    </a>
                                </th>
                            @endforeach

                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $max = collect($comentarios)->map(fn($d) => count($d))->max() ?? 0;
                        @endphp

                        @for ($i = 0; $i < $max; $i++)
                            <tr class="">
                                @foreach ($comentarios as $loopIndex => $dados)
                                    @php
                                        $c = $dados[$i] ?? null;
                                    @endphp

                                    @if ($c)
                                        <td
                                            class="border border-gray-300 w-[10px] text-left text-gray-800 text-[10px] ">
                                            {{ $i + 1 }}</td>
                                        <td
                                            class="border border-gray-300 max-w-[800px] text-sm whitespace-nowrap  overflow-hidden text-ellipsis">
                                            {{ \Illuminate\Support\Str::words(strip_tags($c['text'] ?? '[sem texto]'), 10, '...') }}
                                        </td>
                                        <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                            {{ $c['likeCount'] ?? 0 }}</td>
                                        <td class="border border-gray-300 w-[20px] text-gray-800 text-[10px]">
                                            {{ isset($c['publishedAt']) ? \Carbon\Carbon::parse($c['publishedAt'])->format('d/m/Y') : '--' }}
                                        </td>
                                        <td class="border border-gray-300 w-[10px] text-gray-800 text-[10px]">
                                            {{ isset($c['toxicity']) ? number_format($c['toxicity'] * 100, 1) . '%' : 'X' }}
                                        </td>
                                    @else
                                        <td colspan="5"
                                            class="border border-gray-300 w-full text-gray-900 text-center italic text-[11px]">
                                            ---</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>



                    <tr class="bg-white">
                        @foreach ($comentarios as $video_id => $dados)
                            @php
                                $maxHeight = 100;
                            @endphp
                            <td colspan="5" class="border border-gray-300 text-center">
                                <div class="relative w-full h-[{{ $maxHeight }}px] bg-gray-100 overflow-hidden">
                                    @foreach ($dados as $index => $c)
                                        @php
                                            $tox = $c['toxicity'] ?? null;
                                            $height = $tox ? intval($tox * $maxHeight) : 0;
                                            $left = $index * 2; // espaçamento horizontal
                                            $color =
                                                $tox >= 0.7
                                                    ? 'bg-red-500'
                                                    : ($tox >= 0.4
                                                        ? 'bg-yellow-400'
                                                        : 'bg-green-400');
                                        @endphp

                                        @if ($tox)
                                            <div class="absolute bottom-0 {{ $color }}"
                                                style="left: {{ $left }}px; width: 1px; height: {{ $height }}px;"
                                                title="Tox: {{ number_format($tox * 100, 1) }}%">{{ $tox }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    <tfoot>
                        <tr
                            class="bg-gray-50 border-t border-gray-300 text-[11px] text-gray-700 font-semibold text-center">
                            @foreach ($comentarios as $video_id => $dados)
                                @php
                                    $toxMedia = collect($dados)->pluck('toxicity')->filter()->avg();
                                @endphp
                                <td colspan="5" class="border border-gray-300 py-1">
                                    Toxicidade média: {{ $toxMedia ? number_format($toxMedia * 100, 1) . '%' : 'n/a' }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>

                </table>
            </div>


        </div>
    </div>
</div>
