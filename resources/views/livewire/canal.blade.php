<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Canais') }}
        </h2>
    </x-slot>
 
    <div class="py-2">
        <div class="mx-auto max-w-12xl sm:px-2 lg:px-2">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">
                    
                    <x-msg />

                    <div class="flex items-center justify-around mb-3">
                        <div class="flex items-center space-x-4">
                            <x-input-label>Search</x-input-label>
                            <x-text-input wire:model.live.debounce.500ms="search" name="q" /></div>
                        <div>
                            <div class="flex space-x-4 items-center">
                                <x-input-label>Per page</x-input-label>
                                <select wire:model.live="perPage" class="rounded">
                                    <option value="10">10</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                        </div>
                        <div class="">{{ $canals->links() }}</div>
                    </div>
                    


                    <div class="w-full align-middle">
                        <table class="w-full border divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th wire:click="doSort('id')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="id" :sortColumn="$sortColumn" :sortDirection="$sortDirection" /> 
                                    </th>
                                    <th wire:click="doSort('slug')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="slug" :sortColumn="$sortColumn" :sortDirection="$sortDirection" /> 
                                    </th>
                                    <th wire:click="doSort('nome')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="nome" :sortColumn="$sortColumn" :sortDirection="$sortDirection" /> 
                                    </th>
                                    <th wire:click="doSort('youtube_id')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="youtube_id" :sortColumn="$sortColumn" :sortDirection="$sortDirection" /> 
                                    </th>
                                    
                                     <th class="px-2 py-1 text-left bg-gray-50">desc</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">local</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">dt</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">views</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">videos</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">categ</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">inscr</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">busca</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">min</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">max</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">score</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">videos</th>
                                     <th class="px-2 py-1 text-left bg-gray-50">arxivs</th>
                                    
                                    
    
                                </tr>
                            </thead>
 
                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($canals as $canal)
                                    
                                    <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-200" wire:key="canal-{{ $canal->id }}">
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $canal->id }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a href="http://youtube.com{{ $canal->cod }}" 
                                                class="underline hover:no-underline text-blue-600 hover:text-blue-900 visited:text-purple-600"
                                                target="_blank">
                                                {{ Str::limit($canal->slug,15) }}
                                            </a>
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                           
                                           
                                            {{ $canal->nome }}
                                        </td>
                                        
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ Str::limit($canal->youtube_id, 6, '...')  }}
                                        </td>
                                        
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <span class="cursor-zoom-in" title="{{ $canal->desc }}"> {{ Str::limit($canal->desc, 40, '...')  }} </span>
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $canal->local }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $canal->dt ? $canal->dt->format('Y/M') : '' }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ kmbt($canal->views) }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ kmbt($canal->videos) }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $canal->categ }}
                                        </td>

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ kmbt($canal->inscritos) }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $canal->busca->slug ?? '' }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ Number::currency($canal->min ?? 0) }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ Number::currency($canal->max ?? 0) }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $canal->score }}
                                        </td>

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $canal->videos()->count() }}
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            A:<a href="{{ route('arxiv',$canal->id) }}">{{ $canal->arxivs()->count() }}</a>
                                            
                                        </td>
                                       
                                                                           

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="Url('{{ $canal->id }}')" class="text-blue-700 font-semibold" 
                                                href="#">Url</a>
                                                <div wire:loading wire:target="Url('{{ $canal->id }}')">
                                                    ...
                                                </div>
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="API('{{ $canal->id }}')" class="text-blue-700 font-semibold" 
                                                href="#">Api</a>
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="clear()" class="text-blue-700 font-semibold" 
                                                href="#">X</a>
                                        </td>

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a class="text-blue-700 font-semibold" 
                                                href="{{ route('graf',$canal) }}">Graf1</a>
                                        </td>

                                      
                                      




                                        @if ($canal->youtube_id)
                                            
                                            <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <button class="text-yellow-700 font-semibold"
                                                wire:click.prevent="$dispatch('openModal', { component: 'manual', arguments: { canal: {{ $canal }} }})">
                                                Man</button>
                                            </td>

                                            <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <a wire:click.prevent="vidiq('{{ $canal->id }}')" class="text-purple-700 font-semibold" 
                                                    href="#">Vidiq</a>
                                                <div wire:loading wire:target="vidiq('{{ $canal->id }}')">
                                                    ...
                                                </div>
                                            </td>

                                            <td class="px-2 py-1 text-sm leading-5 text-green-900 whitespace-no-wrap">
                                                <a wire:click.prevent="arxiv1('{{ $canal->id }}')" 
                                                    class="text-green-700 font-semibold" 
                                                    href="#">Arx1</a>
                                                    <div wire:loading wire:target="arxiv1('{{ $canal->id }}')">
                                                        ...
                                                    </div>
                                            </td>
                                            <td class="px-2 py-1 text-sm leading-5 text-green-900 whitespace-no-wrap">
                                                <a wire:click.prevent="arxiv2('{{ $canal->id }}')" 
                                                    class="text-green-700 font-semibold" 
                                                    href="#">Arx2</a>
                                                    <div wire:loading wire:target="arxiv2('{{ $canal->id }}')">
                                                        ...
                                                    </div>
                                            </td>


                                        @endif

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <x-danger-button class="" wire:click.prevent="del('{{ $canal->id }}')">del</x-danger-button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr class="bg-white">
                                        <td colspan="3" class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            No canals found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div wire:stream="out">{{ $out }}</div>


                    <x-secondary-button wire:click="craw" class="mt-3">
                        {{ __('Acoes divs') }}
                    </x-secondary-button>

                    <div class="w-full bg-gray-300" wire:stream="result">
                        {{ $content ?? '' }}
                    </div>

                 </div>
            </div>
        </div>
    </div>
</div>

