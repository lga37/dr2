@props([
    // comportamento
    'variant' => 'video', // 'video' | 'canal'
    'showAdd' => true, // exibir bloco "Adicionar por ID/URL"
    // nomes das props/ações no Livewire PAI
    'queryModel' => 'query',
    'onSearch' => 'pesquisar',
    'addModel' => 'addInput',
    'onAdd' => 'addVideoByInput',
    // placeholders (deixa em branco p/ usar os padrões por variant)
    'queryPlaceholder' => null,
    'addPlaceholder' => null,
    // largura dos inputs
    'widthClass' => 'w-96', // ou 'w-full'
])
@php
    $isCanal = $variant === 'canal';
    $qPh = $queryPlaceholder ?? ($isCanal ? 'Tema ou Canal específico' : 'Tema ou Vídeo específico');
    $aPh = $addPlaceholder ?? ($isCanal ? 'Colar URL, ID ou @handle do canal' : 'Colar URL ou ID do vídeo');
@endphp
<div class="mb-6 flex flex-wrap items-center gap-3 align-middle">
    {{-- Busca --}}
    <form wire:submit.prevent="{{ $onSearch }}">
        <x-text-input class="mt-1 {{ $widthClass }}" placeholder="{{ $qPh }}" autocomplete="off"
            wire:model="{{ $queryModel }}" />
        <x-primary-button type="button" wire:click="{{ $onSearch }}">
            {{ __('Pesquisar') }}
        </x-primary-button>
    </form>

    @if ($showAdd)
        <span class="mx-2">OU</span>
        <form wire:submit.prevent="{{ $onAdd }}">

            <x-text-input class="mt-1 {{ $widthClass }} border rounded" placeholder="{{ $aPh }}"
                wire:model="{{ $addModel }}" />
            <x-primary-button type="button" class="ms-1" wire:loading.attr="disabled"
                wire:click="{{ $onAdd }}">
                Adicionar por ID/URL
            </x-primary-button>
    @endif
    </form>
</div>
