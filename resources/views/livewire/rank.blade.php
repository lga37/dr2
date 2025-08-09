<x-modal>
    <div class="p-4">
        <h2 class="text-xl font-bold">Teste de Modal</h2>
        <pre>{{ var_export($ranking, true) }}</pre>
    </div>
</x-modal>








<!-- 
<div class="p-6">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Ranking de Palavras</h2>

    @if(count($ranking))
        @php $max = max($ranking); @endphp

        <div class="space-y-2">
            @foreach($ranking as $palavra => $frequencia)
                <div>
                    <div class="flex justify-between text-sm text-gray-700 font-medium mb-1">
                        <span class="capitalize">{{ $palavra }}</span>
                        <span>{{ $frequencia }}</span>
                    </div>
                    <div class="h-2 w-full bg-gray-200 rounded overflow-hidden">
                        <div class="h-full bg-blue-500" style="width: {{ ($frequencia / $max) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">Nenhuma palavra relevante encontrada.</p>
    @endif
</div> -->


