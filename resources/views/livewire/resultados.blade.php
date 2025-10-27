{{-- resources/views/livewire/resultados.blade.php --}}
<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Seus resultados
        </h2>
    </x-slot>

    <x-msg />

    <ul class="flex gap-3 text-sm">
        <li><button x-on:click="tab='T1'">Tarefa 1 ({{ $qtd['T1'] }})</button></li>
        <li><button x-on:click="tab='T2'">Tarefa 2 ({{ $qtd['T2'] }})</button></li>
        <li><button x-on:click="tab='T3'">Tarefa 3 ({{ $qtd['T3'] }})</button></li>
    </ul>

    {{-- T1 --}}
    <div x-show="tab==='T1'" class="space-y-8" x-cloak>
        @forelse ($itensByTipo['T1'] as $t)
            @include('components._card_t1', ['t' => $t])
        @empty
            <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
                Você ainda não concluiu nenhuma Tarefa 1.
            </div>
        @endforelse
    </div>

    {{-- T2 --}}
    <div x-show="tab==='T2'" class="space-y-8" x-cloak>
        @forelse ($itensByTipo['T2'] as $t)
            @include('components._card_generico', ['t' => $t, 'titulo' => 'Tarefa 2'])
        @empty
            <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
                Você ainda não concluiu nenhuma Tarefa 2.
            </div>
        @endforelse
    </div>

    {{-- T3 --}}
    <div x-show="tab==='T3'" class="space-y-8" x-cloak>
        @forelse ($itensByTipo['T3'] as $t)
            @include('components._card_generico', ['t' => $t, 'titulo' => 'Tarefa 3'])
        @empty
            <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
                Você ainda não concluiu nenhuma Tarefa 3.
            </div>
        @endforelse
    </div>

</div>
