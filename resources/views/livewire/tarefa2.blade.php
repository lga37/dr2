<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tarefa2 - Analise temporal de sentimento do conteudo gerado pelo usuario (UGC)') }}
        </h2>
        <p>
            A Tarefa 2 tem como objetivo analisar a evolução do sentimento associado aos vídeos. 
            Para isso, todo o acervo de vídeos públicos dos canais selecionados é coletado e, para cada item, aplica-se uma 
            análise de linguagem natural (NLP) no título e na descrição [-1;+1]. Permitindo quantificar a polaridade discursiva 
            do conteúdo. O resultado possibilita observar tendências temporais de polaridade, identificar momentos de maior ou 
            menor positividade/negatividade no discurso e, potencialmente, correlacionar essas variações com eventos, 
            mudanças editoriais ou temas abordados pelo canal.
        </p>







    </x-slot>

    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">
                    <div class="min-w-full align-middle">
                        <div class="flex items-center mb-3">
                            <x-input-label for="busca" class="mr-1" :value="__('Query')" />
                            <x-text-input id="busca" autocomplete="off" wire:model.lazy="query" class="mt-1 w-40" />
                            <x-primary-button class="ms-3" type="button" onclick="document.getElementById('busca').focus()">
                                {{ __('Pesquisar') }}
                            </x-primary-button>
                        </div>
                        @error('query')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <x-input-error :messages="$errors->get('busca')" class="mt-2" />
                    </div>

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
                                    <input type="checkbox" wire:model="selecionados" value="{{ $canal['canalId'] }}">
                                </td>
                                <td class="border px-2 py-1 text-left">{{ $canal['canalId'] ?? '' }}</td>

                                <td class="border px-2 py-1 text-left">
                                    <a href="https://www.youtube.com/channel/{{ $canal['canalId'] }}" target="_blank" class="text-blue-600 hover:underline">
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


                     <div class="mt-4">
                        <x-primary-button wire:click="buscarVideos" wire:loading.attr="disabled">
                            Buscar Videos dos Selecionados
                        </x-primary-button>
                        <div wire:loading wire:target="buscarVideos" class="text-sm text-gray-500 mt-2">
                            Carregando resultados...
                        </div>
                    </div>

                   
                    @endif



                   


                </div>

            </div>


            <!-- tabela dos videos -->
            <div class="overflow-x-auto mt-8">
                <table class="table-auto border border-gray-300 w-full text-[11px] leading-tight">
                    <thead>
                        <tr class="bg-gray-100 text-gray-800 text-center">
                            @foreach($canais as $canal_id => $dados)
                            @php
                            $numCanais = count($canais);
                            $colWidth = number_format(100 / ($numCanais * 7), 2); // 7 colunas por canal agora
                            @endphp
                            <th colspan="7" style="width: {{ $colWidth * 7 }}%;" class="border border-gray-300 px-2 py-1 font-semibold">
                                <a href="https://www.youtube.com/channel/{{ $canal_id }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ $canal_id }}
                                </a>
                            </th>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-200 text-gray-700 text-center">
                            @foreach($canais as $canal_id => $dados)
                            <th class="border border-gray-300 px-1">#</th>
                            <th class="border border-gray-300 px-1">Título</th>
                            <th class="border border-gray-300 px-1">Views</th>
                            <th class="border border-gray-300 px-1">Likes</th>
                            <th class="border border-gray-300 px-1">Comentários</th>
                            <th class="border border-gray-300 px-1">Duração</th>
                            <th class="border border-gray-300 px-1">Upload</th>
                            
                            <th class="border border-gray-300 px-1">NLP tit</th>
                            <th class="border border-gray-300 px-1">NLP desc</th>
                            <th class="border border-gray-300 px-1">NLP</th>

                            

                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $max = collect($canais)->map(fn($d) => count($d))->max() ?? 0;
                        @endphp

                        @for($i = 0; $i < $max; $i++)
                            <tr class="text-center">
                            @foreach($canais as $canalId => $videos)
                            @php
                            $v = $videos[$i] ?? null;
                            @endphp

                            @if($v)
                            <td class="border border-gray-300 px-1 text-gray-800">{{ $i + 1 }}</td>
                            <td class="border border-gray-300 text-left px-1 text-blue-600 hover:underline">
                                <a href="https://www.youtube.com/watch?v={{ $v['videoId'] }}" target="_blank">
                                    {{ \Illuminate\Support\Str::words($v['title'] ?? '[sem título]', 10, '...') }}
                                </a>
                            </td>
                            <td class="border border-gray-300 px-1">{{ number_format($v['viewCount'] ?? 0) }}</td>
                            <td class="border border-gray-300 px-1">{{ number_format($v['likeCount'] ?? 0) }}</td>
                            <td class="border border-gray-300 px-1">{{ number_format($v['commentCount'] ?? 0) }}</td>
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
                            <td colspan="7" class="border border-gray-300 text-gray-400 italic">[sem vídeo]</td>
                            @endif
                            @endforeach
                            </tr>
                            @endfor
                    </tbody>
                </table>
            </div>


        </div>
    </div>
</div>