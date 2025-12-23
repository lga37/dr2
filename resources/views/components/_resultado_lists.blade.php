@php($p = $t['payload'] ?? [])

{{-- CANAIS --}}
@if (!empty($p['canais']))
    <div class="mb-6">
        <div class="text-xs font-semibold text-slate-700 mb-2">Canais</div>

        <div class="space-y-2">
            @forelse ($p['canais'] as $c)
                <div class="rounded-lg border p-3">
                    <div class="font-semibold text-sm">{{ $c['nome'] ?? '—' }}</div>
                    <div class="text-xs text-slate-600">
                        Inscritos: {{ $c['inscritos'] ?? '—' }} · Views: {{ $c['views'] ?? '—' }} · Vídeos: {{ $c['videos'] ?? ($c['videos_qt'] ?? '—') }}
                    </div>

                    {{-- T3: vídeos estão DENTRO de cada canal --}}
                    @if (!empty($c['videos']) && is_array($c['videos']))
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-[800px] w-full text-sm">
                                <thead class="text-xs text-slate-500">
                                    <tr class="border-b">
                                        <th class="text-left py-2 pr-3">Data</th>
                                        <th class="text-left py-2 pr-3">Vídeo</th>
                                        <th class="text-right py-2 pr-3">Views</th>
                                        <th class="text-right py-2 pr-3">Likes</th>
                                        <th class="text-right py-2">Comentários</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($c['videos'] as $v)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $v['dt'] ?? '—' }}</td>
                                            <td class="py-2 pr-3">
                                                <div class="font-medium text-slate-800">{{ $v['nome'] ?? '—' }}</div>
                                                <div class="text-xs text-slate-500">{{ $v['cod'] ?? '' }}</div>
                                            </td>
                                            <td class="py-2 pr-3 text-right">{{ $v['views'] ?? 0 }}</td>
                                            <td class="py-2 pr-3 text-right">{{ $v['likes'] ?? 0 }}</td>
                                            <td class="py-2 text-right">{{ $v['comments'] ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-sm text-slate-500">Sem canais.</div>
            @endforelse
        </div>
    </div>
@endif


{{-- VÍDEOS (T1/T2/T4: vídeos no topo) --}}
@if (!empty($p['videos']))
    <div class="mb-6 overflow-x-auto">
        <div class="text-xs font-semibold text-slate-700 mb-2">Vídeos</div>

        <table class="min-w-[900px] w-full text-sm">
            <thead class="text-xs text-slate-500">
                <tr class="border-b">
                    <th class="text-left py-2 pr-3">Data</th>
                    <th class="text-left py-2 pr-3">Vídeo</th>
                    <th class="text-left py-2 pr-3">Canal</th>
                    <th class="text-right py-2 pr-3">Views</th>
                    <th class="text-right py-2 pr-3">Likes</th>
                    <th class="text-right py-2">Comentários</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($p['videos'] as $v)
                    <tr>
                        <td class="py-2 pr-3 text-slate-600">{{ $v['dt'] ?? '—' }}</td>
                        <td class="py-2 pr-3">
                            <div class="font-medium text-slate-800">{{ $v['nome'] ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ $v['cod'] ?? '' }}</div>
                        </td>
                        <td class="py-2 pr-3 text-slate-700">{{ $v['canal'] ?? '—' }}</td>
                        <td class="py-2 pr-3 text-right">{{ $v['views'] ?? 0 }}</td>
                        <td class="py-2 pr-3 text-right">{{ $v['likes'] ?? 0 }}</td>
                        <td class="py-2 text-right">{{ $v['comments'] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-500">Sem vídeos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif


{{-- COMENTÁRIOS --}}
@if (!empty($p['comentarios']))
    <div class="rounded-xl border p-4">
        <div class="text-xs font-semibold text-slate-700 mb-2">Comentários</div>

        <div class="space-y-2 max-h-[420px] overflow-auto">
            @forelse ($p['comentarios'] as $c)
                <div class="rounded-lg bg-slate-50 border p-3 text-xs text-slate-700">
                    <div class="flex flex-wrap gap-3 text-slate-600">
                        <span><b>dt</b>: {{ $c['dt'] ?? '—' }}</span>
                        <span><b>video_id</b>: {{ $c['video_id'] ?? '—' }}</span>
                        <span><b>likes</b>: {{ $c['likes'] ?? 0 }}</span>
                        <span><b>tox</b>: {{ $c['tox'] ?? '—' }}</span>
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-500">Sem comentários.</div>
            @endforelse
        </div>
    </div>
@endif
