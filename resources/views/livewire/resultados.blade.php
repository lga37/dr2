{{-- resources/views/livewire/resultados.blade.php --}}
<div x-data="{ tab: 'T1' }">  {{-- <<< AQUI: cria a variável "tab" --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Seus resultados
        </h2>
    </x-slot>

    <x-msg />

    <ul class="flex gap-3 text-sm">
        <li><button @click="tab='T1'" :class="tab==='T1' && 'font-semibold underline'">
            Tarefa 1 ({{ $qtd['T1'] }})
        </button></li>
        <li><button @click="tab='T2'" :class="tab==='T2' && 'font-semibold underline'">
            Tarefa 2 ({{ $qtd['T2'] }})
        </button></li>
        <li><button @click="tab='T3'" :class="tab==='T3' && 'font-semibold underline'">
            Tarefa 3 ({{ $qtd['T3'] }})
        </button></li>
    </ul>

    {{-- T1 --}}
    <div x-show="tab==='T1'" x-cloak class="space-y-8">
        @forelse ($itensByTipo['T1'] as $t)
            @include('components._card_t1', ['t' => $t])
        @empty
            <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
                Você ainda não concluiu nenhuma Tarefa 1.
            </div>
        @endforelse
    </div>

    {{-- T2 --}}
    <div x-show="tab==='T2'" x-cloak class="space-y-8">
        @forelse ($itensByTipo['T2'] as $t)
            @include('components._card_t2', ['t' => $t])

        @empty
            <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
                Você ainda não concluiu nenhuma Tarefa 2.
            </div>
        @endforelse
    </div>

    {{-- T3 --}}
    <div x-show="tab==='T3'" x-cloak class="space-y-8">
        @forelse ($itensByTipo['T3'] as $t)
            @include('components._card_t3', ['t' => $t])

        @empty
            <div class="rounded-xl border bg-white p-6 text-center text-slate-600">
                Você ainda não concluiu nenhuma Tarefa 3.
            </div>
        @endforelse
    </div>
</div>
