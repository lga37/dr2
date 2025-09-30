{{-- @props([
    'img' => '',
    'tipo'  => 'peq', #gde
])
@if($tipo=='gde')
<img class="w-40 h-24 object-cover rounded-md" src="{{ $img }}" alt="thumb">
@else
      <img src="{{ $img }}" alt="thumb" class="w-20">
@endif --}}


{{-- resources/views/components/imagem.blade.php --}}
@props([
    'src'   => '',
    'alt'   => '',           // descrição opcional
    'tipo'  => 'peq',        // peq | md | gde | full | square
    'class' => '',           // classes extras
])

@php
    // Mapa de presets (Tailwind)
    $sizes = [
        'peq'    => 'w-20 h-12',          // 80x48 aprox
        'md'     => 'w-32 h-20',          // 128x80
        'gde'    => 'w-40 h-24',          // 160x96
        'full'   => 'w-full h-40',        // largura total, altura fixa
        'square' => 'w-20 h-20',          // quadrada
    ];

    $base = $sizes[$tipo] ?? $sizes['peq'];
    // classes utilitárias padrão
    $frame = implode(' ', [
        $base,
        'overflow-hidden rounded-md bg-gray-100',
        'ring-1 ring-gray-200/60',
    ]);

    // imagem: cobre o contêiner, com suavização
    $imgClass = implode(' ', [
        'w-full h-full object-cover',
        'transition-opacity duration-200',
        $class,
    ]);

    // fallback simples (um pixel transparente) caso src venha vazio
    $fallback = 'data:image/gif;base64,R0lGODlhAQABAAAAACw=';
@endphp

<div class="{{ $frame }}">
    <img
        src="{{ $src ?: $fallback }}"
        alt="{{ $alt }}"
        class="{{ $imgClass }} opacity-0"
        loading="lazy"
        decoding="async"
        onload="this.classList.remove('opacity-0')"
        onerror="this.onerror=null; this.src='{{ $fallback }}';"
    >
</div>
