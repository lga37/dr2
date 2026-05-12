@props([
    'videoId' => null,
    'titulo'  => '',
])

@php
    // monta a URL do canal https://www.youtube.com/watch?v=wHV40sIILuo
    $href = $videoId ? "https://www.youtube.com/watch?v={$videoId}" : '#';
@endphp

<a href="{{ $href }}" target="_blank"
   class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
    {{ $titulo ?: '--' }}
</a>
