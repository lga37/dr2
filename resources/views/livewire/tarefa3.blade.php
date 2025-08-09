<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tarefa3 - Evolucao de Engajamento e Financeira') }}
        </h2>

        <p>
            A Tarefa 3 tem como objetivo analisar a evolução do engajamento e do potencial financeiro de canais do
            YouTube ao
            longo do tempo. Para isso, são coletados dados históricos do número de inscritos por meio do Web Archive,
            permitindo reconstruir a trajetória de crescimento da audiência. Com base nessa série temporal, é possível
            identificar POIs (Pontos de Interesse), como a adesão ao Programa de Parcerias do YouTube, a conquista de
            placas de prata e ouro, ou períodos de crescimento acelerado. A partir dessas informações, realiza-se uma
            inferência sobre a monetização, estimando a evolução financeira do canal de forma aproximada e
            contextualizada. Essa análise combina métricas de crescimento com marcos significativos para oferecer um
            panorama histórico do potencial econômico e de alcance do canal.


        </p>




    </x-slot>

    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">
                    <div class="min-w-full align-middle">
                        <div class="flex items-center mb-3">
                            <x-input-label for="busca" class="mr-1" value="Canal" />
                            <x-text-input id="busca" autocomplete="off" wire:model.lazy="query"
                                class="mt-1 w-1/3" />

                            <x-primary-button class="ms-3" type="button"
                                onclick="document.getElementById('busca').focus()">
                                {{ __('Selecionar') }}
                            </x-primary-button>
                        </div>
                        @error('query')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <x-input-error :messages="$errors->get('busca')" class="mt-2" />
                    </div>

                    <div class="min-w-full align-middle">
                        <div class="flex items-center mb-3">
                            <x-input-label for="busca" class="mr-1" :value="__('Query')" />
                            <x-text-input id="busca" autocomplete="off" wire:model.lazy="query" class="mt-1 w-40" />
                            <x-primary-button class="ms-3" type="button"
                                onclick="document.getElementById('busca').focus()">
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

                                            <input type="checkbox" value="{{ $canal['canalId'] }}"
                                                wire:click="toggleCanal('{{ $canal['canalId'] }}')"
                                                @if (in_array($canal['canalId'], $selecionados)) checked @endif>



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







                    @if (!empty($canais))
                        <h1 class="p-10 font-semibold">Canais selecionados</h1>
                        <table class="min-w-full text-sm border divide-y divide-gray-300 table-auto">

                            <tbody class="text-center">
                                @foreach ($canais as $canal)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-2 py-1">
                                            <input type="checkbox" value="{{ $canal['canalId'] }}"
                                                wire:click="toggleCanal('{{ $canal['canalId'] }}')"
                                                @if (in_array($canal['canalId'], $selecionados)) checked @endif>

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


                        <div class="mt-4">
                            <x-primary-button wire:click="buscarMonets" wire:loading.attr="disabled">
                                Efetuar Analise
                            </x-primary-button>
                            <button wire:click="limparCanais"
                                class="bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700">
                                Limpar Tudo
                            </button>

                            <div wire:loading wire:target="buscarVideos" class="text-sm text-gray-500 mt-2">
                                Carregando resultados...
                            </div>
                        </div>
                    @endif

                </div>
            </div>


            <!-- tabela das monets e inscritos -->
            <div class="overflow-x-auto mt-8">
                <table class="table-auto border border-gray-300 w-full text-[11px] leading-tight">
                    <thead>
                        <tr class="bg-gray-100 text-gray-800 text-center">
                            @foreach ($monets as $canal_id => $dados)
                                <th colspan="5" class="border border-gray-300 px-2 py-1 font-semibold">
                                    <a href="https://www.youtube.com/channel/{{ $canal_id }}" target="_blank"
                                        class="text-blue-600 hover:underline">
                                        {{ $canal_id }}
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-200 text-gray-700 text-center">
                            @foreach ($monets as $canal_id => $dados)
                                <th class="border border-gray-300 px-1">Min</th>
                                <th class="border border-gray-300 px-1">Max</th>
                                <th class="border border-gray-300 px-1">Freq</th>
                                <th class="border border-gray-300 px-1">Length</th>
                                <th class="border border-gray-300 px-1">Engag.</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            @foreach ($monets as $canal_id => $dados)
                                <td class="border border-gray-300 px-1">{{ $dados['min'] ?? '-' }}</td>
                                <td class="border border-gray-300 px-1">{{ $dados['max'] ?? '-' }}</td>
                                <td class="border border-gray-300 px-1">{{ $dados['frequency'] ?? '-' }}</td>
                                <td class="border border-gray-300 px-1">{{ $dados['length'] ?? '-' }}</td>
                                <td class="border border-gray-300 px-1">{{ $dados['engagement'] ?? '-' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>



                @php
                    // Remonta $arxivs em colunas por canal e acha o maior nº de linhas
                    $cols = [];
                    $maxRows = 0;

                    var_dump($arxivs);

                    foreach ($arxivs ?? [] as $canalId => $pairs) {
                        // $pairs é [ timestamp => inscritos, ... ]
                        $lista = [];
                        foreach ($pairs as $ts => $subs) {
                            // Usa só os 8 primeiros dígitos do timestamp (YYYYMMDD)
                            $dia = null;
                            $tsStr = (string) $ts;
                            if (strlen($tsStr) >= 8) {
                                $ymd = substr($tsStr, 0, 8);
                                try {
                                    $dia = \Carbon\Carbon::createFromFormat('Ymd', $ymd)->format('d/m/Y');
                                } catch (\Exception $e) {
                                    $dia = $tsStr; // fallback
                                }
                            } else {
                                $dia = $tsStr;
                            }

                            $lista[] = ['data' => $dia, 'subs' => $subs];
                        }

                        $cols[$canalId] = array_values($lista);
                        $maxRows = max($maxRows, count($cols[$canalId]));

                        var_dump($cols);
                    }
                @endphp

                <table class="table-auto border border-gray-300 w-full text-[11px] leading-tight mt-4">
                    <thead>
                        <tr class="bg-gray-100 text-gray-800 text-center">
                            @foreach ($cols as $canalId => $lista)
                                <th colspan="2" class="border border-gray-300 px-2 py-1 font-semibold">
                                    <a href="https://www.youtube.com/channel/{{ $canalId }}" target="_blank"
                                        class="text-blue-600 hover:underline">
                                        {{ $canalId }}
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-200 text-gray-700 text-center">
                            @foreach ($cols as $canalId => $lista)
                                <th class="border border-gray-300 px-1">Data</th>
                                <th class="border border-gray-300 px-1">Inscritos</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < $maxRows; $i++)
                            <tr class="text-center">
                                @foreach ($cols as $lista)
                                    @php
                                        $row = $lista[$i] ?? null;
                                    @endphp
                                    <td class="border border-gray-300 px-1">{{ $row['data'] ?? '' }}</td>
                                    <td class="border border-gray-300 px-1">{{ $row['subs'] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>





            </div>


        </div>
    </div>
</div>
