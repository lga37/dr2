<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Toxic') }} - {{ $video->nome }}
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">

                    <div class="min-w-full align-middle">


                    <pre>
{{ print_r($pontos, true) }}
</pre>

<div class="relative w-[800px] h-[300px] bg-white border border-gray-400 overflow-hidden mx-auto">

    {{-- Eixo visual opcional --}}
    <div class="absolute left-0 w-full h-0.5 bg-gray-200 top-[150px]"></div> {{-- linha central Y --}}
    <div class="absolute top-0 h-full w-0.5 bg-gray-200 left-[50px]"></div> {{-- linha vertical X inicial --}}

    @foreach($pontos as $point)
        @php
            $left = round($point['x'] * 12);             // eixo X = semanas
            $top = round(300 - ($point['y'] * 3));       // eixo Y = inverso
        @endphp

        <div
            class="absolute w-[6px] h-[6px] rounded-full"
            style="left: {{ $left }}px; top: {{ $top }}px; background-color: red;"
            title="ID: {{ $point['z'] }} — {{ $point['y'] }}%">
        </div>
    @endforeach

</div>

<p class="text-center text-sm text-gray-600 mt-2">
    Eixo X: semanas desde o 1º comentário · Eixo Y: toxicidade (%) · ID (hover)
</p>






                        
                        <!-- <table class="min-w-full border divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">Id</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">Nome</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">ts</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">Inscr</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">Views</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($pontos as $arxiv)


                                @empty
                                    <tr class="bg-white">
                                        <td colspan="3"
                                            class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            No comments found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table> 
                        -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

