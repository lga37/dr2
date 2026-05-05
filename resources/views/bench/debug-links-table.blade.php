<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monetização externa alternativa via URLs') }}
        </h2>
    </x-slot>

    

    <div class="bg-white rounded shadow p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">URLs nas descrições dos vídeos — médias por canal</h2>

        <table class="w-full border-collapse">
            <thead class="bg-green-100">
                <tr>
                    <th class="border p-2 text-left">Handle</th>
                    <th class="border p-2">Lang</th>
                    <th class="border p-2">Vídeos</th>
                    <th class="border p-2">URLs/vídeo</th>
                    <th class="border p-2">Social</th>
                    <th class="border p-2">Money</th>
                    <th class="border p-2">Short</th>
                    <th class="border p-2">Other</th>
                    <th class="border p-2">MoneyTxt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result as $r)
                    <tr>
                        <td class="border p-2 font-semibold">{{ $r['handle'] }}</td>
                        <td class="border p-2 text-center">{{ $r['lang'] }}</td>
                        <td class="border p-2 text-center">{{ $r['video_count'] }}</td>
                        <td class="border p-2 text-center">{{ number_format($r['video_urls_avg'], 2) }}</td>
                        <td class="border p-2 text-center">{{ number_format($r['video_social_avg'], 2) }}</td>
                        <td class="border p-2 text-center">{{ number_format($r['video_money_avg'], 2) }}</td>
                        <td class="border p-2 text-center">{{ number_format($r['video_short_avg'], 2) }}</td>
                        <td class="border p-2 text-center">{{ number_format($r['video_other_avg'], 2) }}</td>
                        <td class="border p-2 text-center">{{ number_format($r['video_money_text_avg'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-bold mb-4">URLs nas descrições dos canais — URLs únicas por canal</h2>

        <table class="w-full border-collapse">
            <thead class="bg-blue-100">
                <tr>
                    <th class="border p-2 text-left">Handle</th>
                    <th class="border p-2">Lang</th>
                    <th class="border p-2">URLs</th>
                    <th class="border p-2">Social</th>
                    <th class="border p-2">Money</th>
                    <th class="border p-2">Short</th>
                    <th class="border p-2">Other</th>
                    <th class="border p-2">MoneyTxt</th>
                    <th class="border p-2 text-left">Money URLs</th>
                    <th class="border p-2 text-left">Short URLs</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result as $r)
                    <tr>
                        <td class="border p-2 font-semibold">{{ $r['handle'] }}</td>
                        <td class="border p-2 text-center">{{ $r['lang'] }}</td>
                        <td class="border p-2 text-center">{{ $r['channel_urls_count'] }}</td>
                        <td class="border p-2 text-center">{{ $r['channel_social_count'] }}</td>
                        <td class="border p-2 text-center">{{ $r['channel_money_count'] }}</td>
                        <td class="border p-2 text-center">{{ $r['channel_short_count'] }}</td>
                        <td class="border p-2 text-center">{{ $r['channel_other_count'] }}</td>
                        <td class="border p-2 text-center">{{ $r['channel_money_text'] }}</td>
                        <td class="border p-2 text-xs">
                            @foreach($r['channel_money'] as $url)
                                <div class="mb-1 break-all">{{ $url }}</div>
                            @endforeach
                        </td>
                        <td class="border p-2 text-xs">
                            @foreach($r['channel_short'] as $url)
                                <div class="mb-1 break-all">{{ $url }}</div>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-app-layout>