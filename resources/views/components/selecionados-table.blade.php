@props([
    // dados
    'items' => [], // array associativo da sessão
    'type' => 'video', // 'video' | 'canal'

    // actions (métodos Livewire no componente pai)
    'remove' => 'removeSelecionado',
    'clear' => 'clearSelecionados',
    'evaluate' => 'avaliarVideos',

    // regras de quantidade para habilitar botão Avaliar
    'min' => 2,
])

@php
    $count = is_countable($items) ? count($items) : 0;

    // helpers
    $fmtNum = fn($n) => number_format((int) ($n ?? 0), 0, ',', '.');

    $date = function ($v, $field) {
        return !empty($v[$field]) ? \Carbon\Carbon::parse($v[$field])->format('d/m/Y') : '';
    };
@endphp

@if ($count)
    <div class="mt-6">
        <h3 class="mb-2 font-semibold">Na sessão : ({{ $count }})</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto divide-y divide-gray-300 border text-sm">
                <thead>
                    @if ($type === 'video')
                        <tr class="bg-gray-50">
                            <th class="px-2 py-1 text-left">remover</th>
                            <th class="px-2 py-1 text-left">id</th>
                            <th class="px-2 py-1 text-left">busca</th>
                            <th class="px-2 py-1 text-left">título</th>
                            <th class="px-2 py-1 text-left">canal</th>
                            <th class="px-2 py-1 text-left">inscritos</th>
                            <th class="px-2 py-1 text-left">coment. raiz+threads</th>
                            <th class="px-2 py-1 text-left">dt upload</th>
                        </tr>
                    @else
                        <tr class="bg-gray-50">
                            <th class="px-2 py-1 text-left">remover</th>
                            <th class="px-2 py-1 text-left">id</th>
                            <th class="px-2 py-1 text-left">busca</th>
                            <th class="px-2 py-1 text-left">handle</th>
                            <th class="px-2 py-1 text-left">canal</th>
                            <th class="px-2 py-1 text-left">inscritos</th>
                            <th class="px-2 py-1 text-left">vídeos</th>
                            <th class="px-2 py-1 text-left">views</th>
                            <th class="px-2 py-1 text-left">U$/mes</th>
                            <th class="px-2 py-1 text-left">Tox %</th>
                            <th class="px-2 py-1 text-left">dt criação</th>
                        </tr>
                    @endif
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($items as $id => $v)
                        @if ($type === 'video')
                            <tr wire:key="{{ $v['videoId'] }}">
                                <td class="px-2 py-1">
                                    <button class="text-red-600 hover:text-red-800 hover:underline"
                                        wire:click="{{ $remove }}('{{ $v['videoId'] }}')">
                                        remover
                                    </button>
                                </td>
                                <td class="px-2 py-1">{{ $v['videoId'] }}</td>
                                <td class="px-2 py-1">{{ $v['q'] ?? '' }}</td>
                                <td class="px-2 py-1">
                                    <x-linkvideo :videoId="$v['videoId']" :titulo="$v['videoTitle'] ?? '—'" />
                                </td>
                                <td class="px-2 py-1">
                                    <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? '—'" />
                                </td>
                                <td class="px-2 py-1">{{ $fmtNum($v['channelSubs'] ?? 0) }}</td>
                                <td class="px-2 py-1">{{ $fmtNum($v['commentCount'] ?? 0) }}</td>
                                <td class="px-2 py-1">{{ $date($v, 'published') }}</td>
                            </tr>
                        @else
                            @php
                                #dd($v);
                                $chId = $v['channelId'] ?? null;
                            @endphp
                            <tr wire:key="{{ $chId }}">
                                <td class="px-2 py-1">
                                    <button class="text-red-600 hover:text-red-800 hover:underline"
                                        wire:click="{{ $remove }}('{{ $chId }}')">
                                        remover
                                    </button>
                                </td>
                                <td class="px-2 py-1">{{ $id ?? '—' }}</td>
                                <td class="px-2 py-1">{{ $v['q'] ?? '' }}</td>
                                <td class="px-2 py-1">{{ $v['channelHandle'] ?? '—' }}</td>
                                <td class="px-2 py-1">
                                    <x-linkcanal :canalId="$chId" :titulo="$v['channelTitle'] ?? '—'" />
                                </td>
                                <td class="px-2 py-1">{{ $fmtNum($v['channelSubs'] ?? 0) }}</td>
                                <td class="px-2 py-1">{{ $fmtNum($v['channelVideos'] ?? 0) }}</td>
                                <td class="px-2 py-1">{{ $fmtNum($v['channelViews'] ?? 0) }}</td>

                                <td class="px-2 py-1">
                                    <a 
                                        class="text-green-500 hover:underline"
                                    href="https://vidiq.com/youtube-stats/channel/{$chId}/" target="_blank">
                                    {{ $fmtNum($v['monetiz'] ?? '') }}

                                    </a>
                                </td>

                                <td class="px-2 py-1">{{ $fmtNum($v['polariz'] ?? '') }}</td>
                                <td class="px-2 py-1">{{ $date($v, 'channelDt') }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-2 flex items-center gap-3 text-sm">
        @if ($count)
            <x-danger-button wire:click="{{ $clear }}">Limpar sessão</x-danger-button>
        @endif

        @php $disabled = ($count < $min); @endphp

        @unless ($count < $min)
            <x-primary-button wire:click="{{ $evaluate }}" wire:target="{{ $evaluate }}"
                wire:loading.attr="disabled" :disabled="$disabled">
                {{ $type === 'video' ? 'Avaliar Vídeos' : 'Avaliar Canais' }}
            </x-primary-button>

            {{-- texto de "carregando..." enquanto avalia --}}
            <span class="text-gray-500 invisible" wire:loading.class.remove="invisible" wire:target="{{ $evaluate }}">
                Carregando análise, aguarde...
            </span>
        @endunless

        
    </div>
@endif
