<div>

    <x-slot name="header">
        <div x-data="{
            open: JSON.parse(localStorage.getItem('tarefa3_header_open') ?? 'true')
        }" x-init="$watch('open', v => localStorage.setItem('tarefa3_header_open', JSON.stringify(v)))" class="relative">
            <!-- Barra do título + botão -->
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Tarefa3 - Monetização') }}
                </h2>

                <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg border hover:bg-gray-50"
                    :aria-expanded="open" aria-controls="t3-instrucoes">
                    <svg x-show="!open" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" x-cloak>
                        <path d="M10 6l6 6H4l6-6z" />
                    </svg>
                    <svg x-show="open" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" x-cloak>
                        <path d="M10 14L4 8h12l-6 6z" />
                    </svg>
                    <span x-show="open" x-cloak>Ocultar instruções</span>
                    <span x-show="!open" x-cloak>Mostrar instruções</span>
                </button>
            </div>

            <!-- Bloco dobrável -->
            <div id="t3-instrucoes" x-show="open" x-transition.opacity.scale.origin.top x-cloak
                class="bg-white shadow-sm rounded-2xl p-6 md:p-8 border">

                <!-- INICIO -->
                <div class="flex items-start gap-4">
                    <!-- ícone $ -->
                    <svg class="w-12 h-12 shrink-0 text-emerald-600" viewBox="0 0 48 48" fill="none"
                        aria-hidden="true">
                        <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="2" />
                        <!-- “R$” como texto (usa a fonte do sistema) -->
                        <text x="24" y="28" text-anchor="middle"
                            font-family="system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif"
                            font-weight="700" font-size="16" fill="currentColor">
                            R$
                        </text>
                    </svg>

                    <div>
                        <h2 class="text-2xl md:text-3xl font-semibold leading-tight">
                            Tarefa 3 — Evolução de <span class="text-emerald-700">engajamento</span> e <span
                                class="text-emerald-700">rentabilidade</span> por canal
                        </h2>
                        <p class="mt-1 text-slate-600">
                            Você comparará <strong>2 canais</strong> e decidirá qual deles é <strong>mais
                                rentável</strong>
                            — não pelo total acumulado,
                            mas pela <strong>eficiência</strong> da produção: <em>quanto o conteúdo publicado rende
                                por
                                minuto</em>.
                        </p>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- como medimos -->
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <svg class="w-10 h-10 text-indigo-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="14" rx="3" stroke="currentColor"
                                stroke-width="1.5" />
                            <path d="M7 13.5l2.5-2.5L12 13l4-4 1 1-5 5-2.5-2.5L8 14.5l-1-1z" fill="currentColor" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Como estimamos a rentabilidade</h3>
                            <p class="text-slate-600 text-sm">
                                Integramos <strong>duas fontes públicas</strong> (por ex., SocialBlade e VidIQ) e
                                usamos a
                                <em>média</em> do
                                intervalo <strong>mín–máx</strong> de ganhos estimados (CPM/RPM) do período atual.
                                Combinamos isso com:
                                <strong>nº de vídeos</strong> e <strong>minutagem publicada</strong> para estimar
                                <em>R$/min
                                    publicado</em>.
                            </p>
                        </div>
                    </div>

                    <!-- o que será exibido -->
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                        <svg class="w-10 h-10 text-amber-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 18V7m5 11V5m5 13V9m5 9V4" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">O que você verá</h3>
                            <p class="text-slate-600 text-sm">
                                Gráficos com <strong>inscritos no tempo</strong> (com <em>POIs</em>: criação,
                                entrada no
                                YPP, 100k/1M etc.),
                                <strong>uploads e minutagem</strong> e a <strong>eficiência financeira</strong>
                                (ganho
                                estimado por minuto).
                                O foco é a <em>tendência linear</em> recente.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid md:grid-cols-3 gap-4 text-sm">
                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que você faz</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li>Selecione <strong>2 canais</strong> para comparar.</li>
                            <li>Observe os metadados e os gráficos exibidos.</li>
                            <li>Indique <strong>qual é mais rentável</strong> em termos de <em>R$/min de
                                    conteúdo</em>.</li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">O que nós calculamos</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li><strong>Estimativa de ganhos</strong> (média do intervalo mín–máx de duas fontes).
                            </li>
                            <li><strong>POIs</strong> na curva de inscritos: criação do canal, entrada no YPP,
                                placas (100k/1M/10M), etc.</li>
                            <li><strong>Uploads</strong>, <strong>minutagem total</strong> e <strong>R$/min
                                    publicado</strong>.</li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-xl border bg-white">
                        <h4 class="font-semibold mb-2">Assumimos (limitações)</h4>
                        <ul class="list-disc ps-5 text-slate-700 space-y-1">
                            <li><em>Crescimento linear</em> recente (WebArchive instável para séries completas).
                            </li>
                            <li>CPM/RPM de fontes públicas é <em>médio e enviesado ao mercado EUA</em>.</li>
                            <li>Ignoramos vídeos excluídos e monetizações externas (loja, patrocínios, membresias).
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-900 text-sm">
                    <span class="font-semibold">Sua decisão:</span>
                    considere os gráficos e a eficiência estimada (R$/min). Marque o canal com <strong>maior
                        eficiência</strong>
                    — o que transforma melhor a sua produção em receita, independentemente da idade do canal.
                </div>
                <!-- FIM -->

            </div>

        </div>
    </x-slot>

    <x-msg />

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">


            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <x-selecionados-table :items="$selecionados" type="canal" remove="removeSelecionado"
                    clear="clearSelecionados" evaluate="avaliarCanais" :min="2" :max="3" />

                <x-search-add-bar variant="canal" query-model="query" on-search="pesquisarCanais" add-model="addInput"
                    on-add="addCanalByInput" />

                <x-results-table variant="canal" :items="$this->buscas" :selected="array_keys($selecionados ?? [])" />
            </div>




            {{-- INSCRITOS (lado a lado) --}}
            <div class="mt-6">
                <h3 class="font-semibold mb-3">Inscritos no tempo</h3>
                <div id="subsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($t3Charts['channels'] as $i => $ch)
                        <div class="bg-white rounded-xl border p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium">{{ $ch['label'] }} <span
                                        class="text-slate-500 text-xs">({{ $ch['id'] }})</span></div>
                                <a href="{{ $ch['vidiqUrl'] }}" class="text-xs underline text-slate-500"
                                    target="_blank" rel="noopener">vidIQ</a>
                            </div>
                            <div class="h-72"><canvas id="subs-{{ $i }}"></canvas></div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- MONETIZAÇÃO (lado a lado) --}}
            <div class="mt-10">
                <h3 class="font-semibold mb-3">Monetização (média vidIQ)</h3>
                <div id="earnGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($t3Charts['channels'] as $i => $ch)
                        <div class="bg-white rounded-xl border p-4">
                            <div class="font-medium mb-2">{{ $ch['label'] }}</div>
                            <div class="h-72"><canvas id="earn-{{ $i }}"></canvas></div>
                        </div>
                    @endforeach
                </div>
            </div>


         @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const payload = @json($t3Charts);
  if (!payload?.channels?.length || typeof Chart === 'undefined') return;

  const colors = ['#10b981', '#ef4444', '#3b82f6', '#f59e0b', '#8b5cf6'];

  // ---------- helpers ----------
  const fmt  = d => new Date(d).toISOString().slice(0,10);         // 'YYYY-MM-DD'
  const ms   = d => new Date(d).getTime();
  const lerp = (a,b,t) => a + (b-a)*t;

  // 12 rótulos igualmente espaçados + fim (hoje)
  function buildLabels(startDate, endDate) {
    const t0 = ms(startDate), t1 = ms(endDate);
    const N = 12, out = [];
    for (let i=0;i<N;i++) out.push(fmt( lerp(t0,t1, i/(N-1)) ));
    const endStr = fmt(endDate);
    if (out.at(-1) !== endStr) out.push(endStr);
    return out;
  }

  // reta 0 → yFinal em função do tempo
  function seriesLinearZeroTo(labels, startDate, endDate, yFinal) {
    const t0 = ms(startDate), t1 = ms(endDate);
    return labels.map(lbl => {
      const r = Math.min(1, Math.max(0, (ms(lbl)-t0)/Math.max(1, t1-t0)));
      return +(yFinal * r).toFixed(2);
    });
  }

  // Posição de um POI (em Y) sobre a reta 0→subsNow
  // retorna {date, y} se existir no intervalo
  function findPoiOnLinear(startDate, endDate, subsNow, poiY) {
    if (!subsNow || poiY <= 0 || poiY > subsNow) return null;
    const t0 = ms(startDate), t1 = ms(endDate);
    const frac = poiY / subsNow;                      // fração do período
    const tPoi = lerp(t0, t1, frac);
    return { date: fmt(tPoi), y: poiY };
  }

  // guias (pontilhadas)
  const guideV = (x, yMax, color) => ({
    type: 'line', label: '_guide_v',
    data: [{x, y:0}, {x, y:yMax}],
    parsing: false, borderColor: color, borderWidth: 1,
    borderDash: [5,4], pointRadius: 0, tension: 0
  });

  const guideH = (x0, x1, y, color) => ({
    type: 'line', label: '_guide_h',
    data: [{x:x0, y}, {x:x1, y}],
    parsing: false, borderColor: color, borderWidth: 1,
    borderDash: [5,4], pointRadius: 0, tension: 0
  });

  // POIs que queremos marcar
  const POIS = [
    { y:  5000,   label: 'YPP (5.000)',   color: '#16a34a' },
    { y: 100000,  label: 'Silver (100k)', color: '#94a3b8' },
  ];

  payload.channels.forEach((ch, i) => {
    const COLOR    = colors[i % colors.length];
    const CREATED  = ch.createdAt;
    const END      = payload.end;                         // "hoje" vindo do Laravel (YYYY-MM-DD)
    const SUBSNOW  = Number(ch.subsNow) || 0;
    const MEANUSD  = (ch.meanUSD==null ? null : Number(String(ch.meanUSD).replace(',', '.')));

    // eixo X comum aos 2 gráficos
    const labels   = buildLabels(CREATED, END);
    const titleTxt = `De ${fmt(CREATED)} a ${fmt(END)} (hoje)`;

    // ================= INSCRITOS =================
    const subsData = seriesLinearZeroTo(labels, CREATED, END, SUBSNOW);

    // POIs existentes neste canal
    const pois = POIS.map(p => {
      const hit = findPoiOnLinear(CREATED, END, SUBSNOW, p.y);
      return hit ? {...p, hit} : null;
    }).filter(Boolean);

    const subsDs = [
      {
        label: ch.label,
        data: subsData,
        borderColor: COLOR,
        borderWidth: 2,
        tension: 0,
        pointRadius: 0,
        spanGaps: false
      },
      // ponto e tooltips
      ...pois.map(p => ({
        type: 'scatter',
        label: 'POI',
        parsing: false,
        showLine: false,
        data: [{ x: p.hit.date, y: p.y, _label: p.label }],
        pointRadius: 5,
        pointHoverRadius: 7,
        backgroundColor: p.color,
        borderColor: p.color
      })),
      // guias
      ...pois.map(p => guideV(p.hit.date, Math.max(p.y, SUBSNOW), p.color)),
      ...pois.map(p => guideH(labels[0], labels.at(-1), p.y, p.color)),
    ];

    const subsCtx = document.getElementById(`subs-${i}`)?.getContext('2d');
    if (subsCtx) {
      if (window[`__subs_${i}`]) try { window[`__subs_${i}`].destroy(); } catch {}
      window[`__subs_${i}`] = new Chart(subsCtx, {
        type: 'line',
        data: { labels, datasets: subsDs },
        options: {
          responsive: true, maintainAspectRatio: false, animation: false,
          scales: {
            x: { type: 'category', ticks: { maxTicksLimit: 7 } },
            y: { beginAtZero: true, ticks: { maxTicksLimit: 6 }, title: { display: true, text: 'Inscritos' } }
          },
          plugins: {
            legend: { display: false },
            title:  { display: true, text: titleTxt },
            tooltip: {
              callbacks: {
                title: (items) => items?.[0]?.label ?? '',
                label: (ctx) => {
                  const r = ctx.raw || {};
                  if (r._label) return r._label;
                  return `Inscritos: ${ctx.parsed.y.toLocaleString()}`;
                }
              }
            }
          }
        }
      });
    }

    // ================= MONETIZAÇÃO =================
    // começa em 0 no YPP e termina em meanUSD no "hoje"
    const ypp = findPoiOnLinear(CREATED, END, SUBSNOW, 5000); // pode ser null

    const earnDatasets = [];
    if (MEANUSD != null && ypp) {
      // reta única com 2 pontos; usar parsing:false e X iguais aos labels
      earnDatasets.push({
        label: 'US$ / mês (estimado)',
        type: 'line',
        parsing: false,
        data: [{ x: ypp.date, y: 0 }, { x: fmt(END), y: MEANUSD }],
        borderColor: COLOR,
        borderWidth: 2,
        tension: 0,
        pointRadius: 0
      });
      // guia vertical no início da monetização
      earnDatasets.push( guideV(ypp.date, MEANUSD, '#16a34a') );
    } else {
      // antes do YPP ou sem média → nada a mostrar
      earnDatasets.push({
        label: 'Sem dados',
        data: labels.map(()=>null),
        borderColor: COLOR,
        borderWidth: 0,
        pointRadius: 0
      });
    }

    const earnCtx = document.getElementById(`earn-${i}`)?.getContext('2d');
    if (earnCtx) {
      if (window[`__earn_${i}`]) try { window[`__earn_${i}`].destroy(); } catch {}
      window[`__earn_${i}`] = new Chart(earnCtx, {
        type: 'line',
        data: { labels, datasets: earnDatasets },
        options: {
          responsive: true, maintainAspectRatio: false, animation: false,
          scales: {
            x: { type: 'category', ticks: { maxTicksLimit: 7 } },
            y: { beginAtZero: true, ticks: { maxTicksLimit: 6 }, title: { display: true, text: 'US$ / mês' } }
          },
          plugins: {
            legend: { display: false },
            title:  { display: true, text: titleTxt },
            tooltip: {
              callbacks: {
                title: (items) => items?.[0]?.label ?? '',
                label: (ctx) =>
                  (ctx.parsed?.y == null)
                    ? 'Sem dados (antes do YPP)'
                    : `Estimado: $${Number(ctx.parsed.y).toLocaleString(undefined,{maximumFractionDigits:2})}`
              }
            }
          }
        }
      });
    }
  });
});
</script>
@endpush




        </div>
    </div>
</div>
