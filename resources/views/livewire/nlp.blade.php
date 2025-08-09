<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nlp') }} - {{ $busca->q }}
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">

                    
                




                    





                        
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
                                            No buscas found.
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

