<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Relatorio de benchmark de polarizacao') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto">


        {{-- GERAL --}}
        <div class="mb-8 bg-white p-4 rounded shadow">
            <h2 class=" font-semibold mb-3">Resumo Geral</h2>

            <div class="grid grid-cols-4 gap-4">
                <div class="p-3 bg-green-100 rounded">
                    <b>Hit Rate</b><br>
                    {{ $data['geral']['hit_rate'] ?? '-' }}
                </div>

                <div class="p-3 bg-blue-100 rounded">
                    <b>Confidence Avg</b><br>
                    {{ $data['geral']['confidence_avg'] ?? '-' }}
                </div>

                <div class="p-3 bg-yellow-100 rounded">
                    <b>Ambiguous Rate</b><br>
                    {{ $data['geral']['ambiguous_rate'] ?? '-' }}
                </div>

                <div class="p-3 bg-red-100 rounded">
                    <b>Deviation Rate</b><br>
                    {{ $data['geral']['intra_channel_deviation_rate'] ?? '-' }}
                </div>
            </div>
        </div>

<div class="mb-8 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Alinhamento Canal × Conteúdo</h2>

    <table class="w-full text-xl border">
        <thead class="bg-gray-300">
            <tr>
                <th class="p-3">Métrica</th>
                <th class="p-3">Valor</th>
                <th class="p-3">Interpretação</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-t">
                <td class="p-3 font-bold">Hit Rate</td>
                <td class="p-3 text-center">{{ $data['geral']['hit_rate'] ?? '-' }}</td>
                <td class="p-3">Alinhamento entre o rótulo do canal e o rótulo inferido para o vídeo.</td>
            </tr>

            <tr class="border-t">
                <td class="p-3 font-bold">Deviation Rate</td>
                <td class="p-3 text-center">{{ $data['geral']['intra_channel_deviation_rate'] ?? '-' }}</td>
                <td class="p-3">Proporção de vídeos que divergem do perfil esperado do canal.</td>
            </tr>

            <tr class="border-t">
                <td class="p-3 font-bold">Confidence em Desvios</td>
                <td class="p-3 text-center">{{ $data['geral']['confidence_when_deviation'] ?? '-' }}</td>
                <td class="p-3">Mede se o modelo diverge do canal com baixa ou alta segurança.</td>
            </tr>

          

            <tr class="border-t">
                <td class="p-3 font-bold">Desvio Confiante</td>
                <td class="p-3 text-center font-bold">
                    {{ $data['geral']['deviation_confident_rate'] ?? '-' }}
                </td>
                <td class="p-3">Casos em que o modelo discorda do canal com confiança alta e sem ambiguidade.</td>
            </tr>
        </tbody>
    </table>
</div>


{{-- POR LABS - FORMATO PLANILHA --}}
<div class="mb-8 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Tabela de Simulações por Labs</h2>

    <table class="w-full border text-lg">
        <thead>
            <tr class="bg-green-100">
                <th class="p-2 border">Labs</th>
                <th class="p-2 border">Base</th>
                <th class="p-2 border">Video</th>
                <th class="p-2 border">Stats</th>
                <th class="p-2 border">Canal</th>
                <th class="p-2 border">T</th>
                <th class="p-2 border">Hit_Rate</th>
                <th class="p-2 border">Conf_Avg</th>
                <th class="p-2 border">Conf_Hit</th>
                <th class="p-2 border">Conf_Dev</th>
                <th class="p-2 border">Ambig</th>
                <th class="p-2 border">Deviat</th>
                <th class="p-2 border">Sent_Pos</th>
                <th class="p-2 border">Sent_Neg</th>
                <th class="p-2 border">Sent_Int</th>
            </tr>
        </thead>

        <tbody>
        @foreach($data['por_labs'] ?? [] as $labsKey => $row)
            @php
                $labs = explode('+', $labsKey);
            @endphp

            <tr class="border-t">
                <td class="p-2 border font-bold">{{ $labsKey }}</td>

                <td class="p-2 border text-center bg-yellow-50">{{ in_array('base', $labs) ? 'x' : '' }}</td>
                <td class="p-2 border text-center bg-yellow-50">{{ in_array('video', $labs) ? 'x' : '' }}</td>
                <td class="p-2 border text-center bg-yellow-50">{{ in_array('stats', $labs) ? 'x' : '' }}</td>
                <td class="p-2 border text-center bg-yellow-50">{{ in_array('channel', $labs) ? 'x' : '' }}</td>
                <td class="p-2 border text-center bg-yellow-50">{{ in_array('transcript', $labs) ? 'x' : '' }}</td>

                <td class="p-2 border text-center">{{ $row['hit_rate'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['confidence_avg'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['confidence_when_hit'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['confidence_when_deviation'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['ambiguous_rate'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['intra_channel_deviation_rate'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['sentiment_positive_rate'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['sentiment_negative_rate'] ?? '-' }}</td>
                <td class="p-2 border text-center">{{ $row['sentiment_intensity_avg'] ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>



        {{-- POR DIMENSÃO --}}
        <div class="mb-8 bg-white p-4 rounded shadow">
            <h2 class=" font-semibold mb-3">Por Dimensão</h2>

            <table class="w-full border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2">Dimensão</th>
                        <th>Hit</th>
                        <th>Confidence</th>

                    </tr>
                </thead>
                <tbody>
                @foreach($data['por_dimensao'] ?? [] as $dim => $row)
                    <tr class="border-t">
                        <td class="p-2 font-bold">{{ $dim }}</td>

                        <td class="text-center">
                            <span class="px-2 py-1 rounded
                                {{ ($row['hit_rate'] ?? 0) > 0.7 ? 'bg-green-200' : 'bg-red-200' }}">
                                {{ $row['hit_rate'] ?? '-' }}
                            </span>
                        </td>

                        <td class="text-center">{{ $row['confidence_avg'] ?? '-' }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- PROMPT LEVEL x DIMENSÃO --}}
        <div class="bg-white p-4 rounded shadow">
            <h2 class=" font-semibold mb-3">Labs × Dimensão</h2>

            <table class="w-full border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2">Grupo</th>
                        <th>Hit</th>
                        <th>Confidence</th>

                    </tr>
                </thead>
                <tbody>
                @foreach($data['por_labs_e_dimensao'] ?? [] as $key => $row)
                    <tr class="border-t">
                        <td class="p-2">{{ $key }}</td>

                        <td class="text-center">
                            <span class="px-2 py-1 rounded
                                {{ ($row['hit_rate'] ?? 0) > 0.7 ? 'bg-green-200' : 'bg-red-200' }}">
                                {{ $row['hit_rate'] ?? '-' }}
                            </span>
                        </td>

                        <td class="text-center">{{ $row['confidence_avg'] ?? '-' }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>



<div class="mb-8 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Figura: Alinhamento, Desvio e Confiança</h2>

    <canvas id="alignmentChart" height="90"></canvas>
</div>

    

<div class="mb-8 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Dimensão Afetiva da Polarização</h2>

    <table class="w-full text-xl border">
        <thead class="bg-gray-300">
            <tr>
                <th class="p-3">Métrica</th>
                <th class="p-3">Valor</th>
                <th class="p-3">Interpretação</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-t">
                <td class="p-3 font-bold">Sentimento Negativo</td>
                <td class="p-3 text-center">{{ $data['geral']['sentiment_negative_rate'] ?? '-' }}</td>
                <td class="p-3">Proporção de vídeos com valência emocional negativa.</td>
            </tr>

            <tr class="border-t">
                <td class="p-3 font-bold">Sentimento Positivo</td>
                <td class="p-3 text-center">{{ $data['geral']['sentiment_positive_rate'] ?? '-' }}</td>
                <td class="p-3">Proporção de vídeos com valência emocional positiva.</td>
            </tr>

            <tr class="border-t">
                <td class="p-3 font-bold">Intensidade em Hits</td>
                <td class="p-3 text-center">{{ $data['geral']['sentiment_intensity_when_hit'] ?? '-' }}</td>
                <td class="p-3">Intensidade emocional média dos vídeos alinhados ao canal.</td>
            </tr>

            <tr class="border-t">
                <td class="p-3 font-bold">Intensidade em Desvios</td>
                <td class="p-3 text-center">{{ $data['geral']['sentiment_intensity_when_deviation'] ?? '-' }}</td>
                <td class="p-3">Intensidade emocional média dos vídeos que divergem do perfil do canal.</td>
            </tr>

            <tr class="border-t">
                <td class="p-3 font-bold">Desvio Emocional</td>
                <td class="p-3 text-center font-bold">{{ $data['geral']['emotional_deviation_rate'] ?? '-' }}</td>
                <td class="p-3">Casos em que o vídeo diverge do canal e apresenta intensidade emocional alta.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="mb-8 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Figura: Sentimento e Desvio Intra-canal</h2>

    <canvas id="sentimentChart" height="90"></canvas>
</div>


</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('alignmentChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            'Hit Rate',
            'Deviation Rate',
            'Confidence Hit',
            'Confidence Deviation',
            'Confident Deviation'
        ],
        datasets: [{
            label: 'Métricas agregadas',
            data: [
                {{ $data['geral']['hit_rate'] ?? 0 }},
                {{ $data['geral']['intra_channel_deviation_rate'] ?? 0 }},
                {{ $data['geral']['confidence_when_hit'] ?? 0 }},
                {{ $data['geral']['confidence_when_deviation'] ?? 0 }},
                {{ $data['geral']['deviation_confident_rate'] ?? 0 }}
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                min: 0,
                max: 1
            }
        }
    }
});




const sentimentCtx = document.getElementById('sentimentChart');

new Chart(sentimentCtx, {
    type: 'bar',
    data: {
        labels: [
            'Negativo',
            'Positivo',
            'Neutro',
            'Intensidade Hit',
            'Intensidade Desvio',
            'Desvio Emocional'
        ],
        datasets: [{
            label: 'Métricas afetivas',
            data: [
                {{ $data['geral']['sentiment_negative_rate'] ?? 0 }},
                {{ $data['geral']['sentiment_positive_rate'] ?? 0 }},
                {{ $data['geral']['sentiment_neutral_rate'] ?? 0 }},
                {{ $data['geral']['sentiment_intensity_when_hit'] ?? 0 }},
                {{ $data['geral']['sentiment_intensity_when_deviation'] ?? 0 }},
                {{ $data['geral']['emotional_deviation_rate'] ?? 0 }}
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                min: 0,
                max: 1
            }
        }
    }
});

</script>    