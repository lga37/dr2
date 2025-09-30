@props([
    'videoId' => null,
    'titulo'  => '',
])

@php
    // monta a URL do canal
    $href = $videoId ? "https://youtube.com/{$videoId}" : '#';
@endphp

<a href="{{ $href }}" target="_blank"
   class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
    {{ $titulo ?: '--' }}
</a>
