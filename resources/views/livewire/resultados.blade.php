{{-- resources/views/livewire/resultados.blade.php --}}
<x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Seus resultados — Tarefa 1
    </h2>
</x-slot>

<x-msg /> {{-- se você já tem um componente de mensagens --}}

<div class="space-y-8">
    @forelse ($itens as $t)
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="text-sm text-gray-500">
                        #{{ $t['id'] }} • feito em {{ $t['quando'] }}
                    </div>
                    <div class="text-lg font-semibold">
                        Duração: <span class="font-bold text-indigo-700">{{ $t['duracao_human'] }}</span>
                    </div>
                </div>

                @if ($t['acertou'])
                    <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">
                        Você acertou
                    </span>
                @else
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-sm font-semibold text-rose-700">
                        Você errou
                    </span>
                @endif
            </div>

            {{-- Buscas (chips) --}}
            @if (!empty($t['buscas']))
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($t['buscas'] as $q)
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs text-gray-700">
                            {{ $q }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                {{-- Vídeos --}}
                <div class="col-span-2">
                    <h3 class="mb-2 text-base font-semibold">Vídeos avaliados</h3>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-3 py-2">Vídeo</th>
                                    <th class="px-3 py-2">Canal</th>
                                    <th class="px-3 py-2 text-right">Views</th>
                                    <th class="px-3 py-2 text-right">Likes</th>
                                    <th class="px-3 py-2 text-right">Com.</th>
                                    <th class="px-3 py-2">Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($t['videos'] as $v)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <a class="text-blue-600 hover:underline" target="_blank"
                                                href="https://www.youtube.com/watch?v={{ $v['cod'] }}">
                                                {{ $v['nome'] ?? $v['cod'] }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2">{{ $v['canal'] ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($v['views'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ number_format($v['likes'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            {{ number_format($v['comments'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2">{{ $v['dt'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Resumo --}}
                <div>
                    <h3 class="mb-2 text-base font-semibold">Resumo</h3>
                    <div class="space-y-2 rounded-lg border p-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Comentários analisados</span>
                            <span class="font-semibold">{{ $t['comentarios_qt'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tox. média (geral)</span>
                            <span class="font-semibold">{{ number_format($t['tox_media'] * 100, 1, ',', '.') }}%</span>
                        </div>

                        {{-- tox por vídeo (opcional, se quiser ver) --}}
                        @if (!empty($t['tox_por_video']))
                            <div class="mt-3">
                                <div class="mb-1 text-xs text-gray-500">Tox. média por vídeo</div>
                                <ul class="space-y-1 text-sm">
                                    @foreach ($t['tox_por_video'] as $vid => $tox)
                                        <li class="flex justify-between">
                                            <span class="text-gray-500">{{ $vid }}</span>
                                            <span
                                                class="font-semibold">{{ number_format($tox * 100, 1, ',', '.') }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- dados gravados no JSON --}}
                        @if (!empty($t['tox_json']))
                            <div class="mt-3">
                                <div class="mb-1 text-xs text-gray-500">Snapshot (JSON)</div>
                                <ul class="space-y-1 text-sm">
                                    @foreach ($t['tox_json'] as $vid => $val)
                                        <li class="flex justify-between">
                                            <span class="text-gray-500">{{ $vid }}</span>
                                            <span class="font-semibold">{{ $val }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- canais --}}
                    @if (!empty($t['canais']))
                        <h3 class="mt-6 mb-2 text-base font-semibold">Canais</h3>
                        <div class="space-y-2 rounded-lg border p-3">
                            @foreach ($t['canais'] as $c)
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ $c['nome'] }}</span>
                                    <span class="text-gray-500">
                                        {{ number_format($c['inscritos'] ?? 0, 0, ',', '.') }} inscritos
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Feedback do usuário (se houver) --}}
            @if ($t['feedback'])
                <div class="mt-6 rounded-lg border bg-slate-50 p-3 text-sm">
                    <div class="mb-1 font-semibold text-slate-700">Seu feedback</div>
                    <p class="text-slate-700">{{ $t['feedback'] }}</p>
                </div>
            @endif
        </div>
    @empty
        <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
            Você ainda não concluiu nenhuma Tarefa 1.
        </div>
    @endforelse
</div>
