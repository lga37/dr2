<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Avaliação da Ferramenta Academica - Bem Vindos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-1 text-gray-900">






                    {{-- resources/views/home.blade.php --}}


                    <div class="relative">
                        {{-- blobs de cor no fundo --}}
                        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
                            <div class="absolute -top-24 right-1/2 h-72 w-72 rounded-full bg-indigo-300/30 blur-3xl">
                            </div>
                            <div class="absolute -bottom-32 left-1/3 h-96 w-96 rounded-full bg-fuchsia-300/30 blur-3xl">
                            </div>
                            <div class="absolute top-1/2 -right-24 h-64 w-64 rounded-full bg-cyan-300/30 blur-3xl">
                            </div>
                        </div>

                        <div class="mx-auto max-w-6xl px-4 py-6 md:py-6">
                            {{-- HERO --}}
                            <header class="mb-6 md:mb-6">
                                <div class="grid items-center gap-10 md:grid-cols-2">
                                    {{-- Texto / badges / CTA --}}
                                    <div>
                                        <div
                                            class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100">
                                            <span class="i-lab">🔬</span> Pesquisa acadêmica • Doutorado em Informática
                                        </div>

                                        <h1 class="mt-4 text-4xl font-bold tracking-tight md:text-5xl">
                                            Metadados & Percepção Online
                                        </h1>

                                        <p class="mt-4 max-w-xl text-slate-600">
                                            Três tarefas rápidas para medir como você <span class="font-semibold">estima
                                                toxicidade</span>,
                                            <span class="font-semibold">reconhece polarização</span> e <span
                                                class="font-semibold">infere monetização</span>
                                            usando apenas metadados (título, descrição, miniatura, tags). Ao final,
                                            mostramos o resultado real.
                                        </p>

                                        <div class="mt-6 flex flex-wrap gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">🤖
                                                IA aplicada</span>
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">📊
                                                Ciência de Dados</span>
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">🎓
                                                Pesquisa doutoral</span>
                                        </div>

                                        <div class="mt-6 flex flex-wrap gap-3">
                                            <a href="{{ url('/tarefa1') }}"
                                                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 font-semibold text-white shadow transition hover:-translate-y-0.5 hover:bg-indigo-500 hover:shadow-lg">
                                                Começar Tarefa 1
                                            </a>
                                            <a href="#tarefas"
                                                class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-slate-50">
                                                Cadastro Rápido
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Painel de GIFs / vídeos (lado direito) --}}
                                    <div class="relative">
                                        {{-- blobs de cor --}}
                                        <div
                                            class="pointer-events-none absolute -top-8 -left-6 h-28 w-28 rounded-full bg-indigo-300/30 blur-2xl">
                                        </div>
                                        <div
                                            class="pointer-events-none absolute -bottom-10 -right-6 h-32 w-32 rounded-full bg-fuchsia-300/30 blur-2xl">
                                        </div>

                                        <div class="grid grid-cols-3 gap-3">
                                            {{-- GIF maior --}}
                                            <figure
                                                class="col-span-2 aspect-[16/10] overflow-hidden rounded-2xl ring-1 ring-slate-200 shadow-lg">
                                                <img src="https://i.ytimg.com/vi/Hxoh1pYJPnE/maxresdefault.jpg"
                                                    alt="Análise com IA sobre metadados"
                                                    class="h-full w-full object-cover" loading="lazy">
                                            </figure>

                                            {{-- GIF alto (grafos/dados) --}}
                                            <figure
                                                class="aspect-[9/14] overflow-hidden rounded-2xl ring-1 ring-slate-200 shadow-lg">
                                                <img src="https://www.dca.com.br/wp-content/uploads/2023/04/futuro-robo-de-inteligencia-artificial-e-cyborg-860x400.jpg"
                                                    alt="Grafos e relações de informação"
                                                    class="h-full w-full object-cover" loading="lazy">
                                            </figure>

                                            {{-- GIF largo (código/ciencia) --}}
                                            {{-- <figure
                                                class="col-span-3 aspect-[16/9] overflow-hidden rounded-2xl ring-1 ring-slate-200 shadow-lg">
                                                <img src="https://thumbs.dreamstime.com/b/aula-de-ci%C3%AAncia-da-computa%C3%A7%C3%A3o-tela-desenvolvendo-o-c%C3%B3digo-php-em-fundo-escuro-do-script-desenvolvedor-software-conceito-247824334.jpg"
                                                    alt="Código e ciência de dados" class="h-full w-full object-cover"
                                                    loading="lazy">
                                            </figure> --}}
                                        </div>

                                        <div
                                            class="absolute bottom-2 right-3 rounded-full bg-white/80 px-3 py-1 text-xs text-slate-600 ring-1 ring-slate-200 shadow">
                                            PPGI UniRio
                                        </div>
                                    </div>
                                </div>
                            </header>



                            <div class="space-y-10 md:space-y-16">
                                {{-- TAREFA 1 --}}
                                <section
                                    class="reveal grid items-center gap-8 rounded-2xl bg-white/70 p-5 shadow-lg ring-1 ring-slate-200 backdrop-blur md:grid-cols-2 md:p-8">
                                    <div class="order-2 md:order-1">
                                        <h2 class="text-2xl font-semibold md:text-3xl">Tarefa 1 — Toxicidade de
                                            audiência</h2>
                                        <p class="mt-3 text-slate-600">
                                            Você escolhe entre 2 a 4 vídeos e indica qual tende a receber
                                            <span class="font-medium">mais comentários tóxicos</span>, apenas olhando
                                            metadados
                                            (título, descrição, thumbnail, tags). Depois calculamos a média real com a
                                            <span class="font-medium">Perspective API</span> e comparamos com seu
                                            palpite.
                                        </p>
                                        <ul class="mt-4 space-y-2 text-slate-600">
                                            <li>• Foco nos <span class="font-medium">comentários raiz</span>.</li>
                                            <li>• Nada de ler os comentários — é só pelos metadados.</li>
                                            <li>• Feedback imediato do acerto/erro e o porquê.</li>
                                        </ul>
                                        <a href="{{ url('/tarefa1') }}"
                                            class="mt-5 inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 font-semibold text-white shadow transition hover:-translate-y-0.5 hover:bg-indigo-500 hover:shadow-lg">
                                            Começar Tarefa 1
                                        </a>
                                    </div>
                                    <div class="order-1 md:order-2">
                                        <img src="https://images.unsplash.com/photo-1588702547923-7093a6c3ba33?q=80&w=1200&auto=format&fit=crop"
                                            alt="Ilustração de vídeo online e interação do público"
                                            class="mx-auto aspect-video w-full rounded-2xl object-cover shadow-xl ring-1 ring-slate-200/60 transition group-hover:scale-[1.02]"
                                            loading="lazy">
                                    </div>
                                </section>

                                {{-- TAREFA 2 --}}
                                <section
                                    class="reveal grid items-center gap-8 rounded-2xl bg-white/70 p-5 shadow-lg ring-1 ring-slate-200 backdrop-blur md:grid-cols-2 md:p-8">
                                    <div class="order-1">
                                        <img src="https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop"
                                            alt="Ilustração de debate e opinião pública"
                                            class="mx-auto aspect-video w-full rounded-2xl object-cover shadow-xl ring-1 ring-slate-200/60 transition group-hover:scale-[1.02]"
                                            loading="lazy">
                                    </div>
                                    <div class="order-2">
                                        <h2 class="text-2xl font-semibold md:text-3xl">Tarefa 2 — Polarização de canais
                                        </h2>
                                        <p class="mt-3 text-slate-600">
                                            Agora, olhando apenas os <span class="font-medium">metadados de um
                                                canal</span>
                                            (descrição, keywords, país, volume de vídeos, engajamento, etc.), você
                                            estima
                                            o <span class="font-medium">nível de polarização</span>. Depois mostramos a
                                            medição
                                            baseada em sinais objetivos do canal.
                                        </p>
                                        <ul class="mt-4 space-y-2 text-slate-600">
                                            <li>• Treine a percepção de sinais de posicionamento.</li>
                                            <li>• Sem assistir aos vídeos; análise só do “cartão de visita”.</li>
                                            <li>• Compara sua leitura com um indicador composto.</li>
                                        </ul>
                                        <a href="{{ url('/tarefa2') }}"
                                            class="mt-5 inline-flex items-center justify-center rounded-xl bg-cyan-600 px-4 py-2 font-semibold text-white shadow transition hover:-translate-y-0.5 hover:bg-cyan-500 hover:shadow-lg">
                                            Começar Tarefa 2
                                        </a>
                                    </div>
                                </section>

                                {{-- TAREFA 3 --}}
                                <section
                                    class="reveal grid items-center gap-8 rounded-2xl bg-white/70 p-5 shadow-lg ring-1 ring-slate-200 backdrop-blur md:grid-cols-2 md:p-8">
                                    <div class="order-2 md:order-1">
                                        <h2 class="text-2xl font-semibold md:text-3xl">Tarefa 3 — Monetização estimada
                                        </h2>
                                        <p class="mt-3 text-slate-600">
                                            Com base nos dados públicos de um canal (views totais, inscritos, volume de
                                            vídeos,
                                            país/nicho) e séries históricas, você estima uma
                                            <span class="font-medium">faixa de monetização</span>. Em seguida, mostramos
                                            uma
                                            projeção simples para comparação.
                                        </p>
                                        <ul class="mt-4 space-y-2 text-slate-600">
                                            <li>• Intuição vs. estimativa baseada em métricas.</li>
                                            <li>• Entenda quais sinais mais “pesam” no resultado.</li>
                                            <li>• Feedback rápido para calibrar sua percepção.</li>
                                        </ul>
                                        <a href="{{ url('/tarefa3') }}"
                                            class="mt-5 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 font-semibold text-white shadow transition hover:-translate-y-0.5 hover:bg-emerald-500 hover:shadow-lg">
                                            Começar Tarefa 3
                                        </a>
                                    </div>
                                    <div class="order-1 md:order-2">
                                        <img src="https://images.unsplash.com/photo-1604594849809-dfedbc827105?q=80&w=1200&auto=format&fit=crop"
                                            alt="Ilustração de finanças e crescimento de receita"
                                            class="mx-auto aspect-video w-full rounded-2xl object-cover shadow-xl ring-1 ring-slate-200/60 transition group-hover:scale-[1.02]"
                                            loading="lazy">
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>










                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Efeito de revelação suave ao rolar --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const els = [...document.querySelectorAll('.reveal')];
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('opacity-100', 'translate-y-0');
                    io.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.15
        });

        els.forEach(el => {
            el.classList.add('opacity-0', 'translate-y-6', 'transition', 'duration-700');
            io.observe(el);
        });
    });
</script>
