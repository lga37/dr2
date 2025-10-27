@props([
    'v'   => $v,

])

<div>
    <div class="flex items-start">
        <x-imagem :src="$v['channelThumb']" tipo="gde" class="shadow-sm" />
        <div class="flex-1 ">
            <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? ''" />

            <div class="h-20 text-xs text-justify text-gray-500 mt-1 line-clamp-4">
                {{ $v['channelDesc'] ?? '' }}
            </div>
        </div>
    </div>

    <h4 class="text-lg h-8 font-semibold mt-4 mb-1">
        Dados do Canal
        <x-linkcanal :canalId="$v['channelId']" :titulo="$v['channelTitle'] ?? ''" />

        — criado em
        {{ isset($v['channelDt']) ? \Carbon\Carbon::parse($v['channelDt'])->format('d/m/Y') : '—' }}
    </h4>

    <x-keywords :items="$v['channelKeywords'] ?? []" limit="8" rows="2" />


    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
        <div class="bg-gray-50 p-2 rounded">
            <div class="text-gray-500">Origem/Pais</div>
            <div class="font-semibold">{{ $v['channelCountry'] ?? '-' }}</div>
        </div>
        <div class="bg-gray-50 p-2 rounded">
            <div class="text-gray-500">Total Views</div>
            <div class="font-semibold">
                {{ number_format($v['channelViews'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-gray-50 p-2 rounded">
            <div class="text-gray-500">Total Vídeos</div>
            <div class="font-semibold">
                {{ number_format($v['channelVideos'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="bg-gray-50 p-2 rounded">
            <div class="text-gray-500">Inscritos</div>
            <div class="font-semibold">
                {{ number_format($v['channelSubs'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

</div>
