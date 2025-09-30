@props([
    'canalId' => null,
    'titulo'  => '',
])

@php
    // monta a URL do canal
    $href = $canalId ? "https://youtube.com/channel/{$canalId}" : '#';
@endphp

<a href="{{ $href }}" target="_blank"
   class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
    {{ $titulo ?: '--' }}
</a>
