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

                            <x-primary-button wire:click="buscarDados" wire:loading.attr="disabled">
                                Buscar Dados dos Selecionados
                            </x-primary-button>
                            <div wire:loading wire:target="buscarDados" class="text-sm text-gray-500 mt-2">
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
                

               

            </div>





        </div>
    </div>
</div>
