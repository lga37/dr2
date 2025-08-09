<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Videos') }}
        </h2>
    </x-slot>

    <x-msg />

    <div class="py-2">
        <div class="mx-auto max-w-12xl sm:px-2 lg:px-2">

            <form method="POST" class="flex items-center mb-3" wire:submit='add'>
                <x-input-label for="url" class="mr-1" :value="__('Vdeo Add')" />
                <x-text-input id="url" class="mt-1 w-40" wire:model.defer="url" />
                <x-primary-button class="ms-3">Add</x-primary-button>
            </form>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">


                    <div class="flex items-center space-x-8 justify-around mb-3">
                        <div class="flex items-center">
                            <x-input-label>Search</x-input-label>
                            <x-text-input wire:model.live.debounce.500ms="search" name="q" />
                        </div>
                        <div>
                            <div class="flex items-center">
                                <x-input-label>Per page</x-input-label>
                                <select wire:model.live="perPage" class="rounded">
                                    <option value="10">10</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <x-input-label>Busca</x-input-label>
                            <select wire:model.live="busca_id" class="rounded">
                                @foreach ($buscas as $busca)
                                    <option value="{{ $busca->id }}">{{ $busca->q }}</option>
                                @endforeach


                            </select>
                        </div>


                        <div class="">{{ $videos->links() }}</div>
                    </div>



                    <div class="min-w-full align-middle">


                        <table class="min-w-full border divide-y divide-gray-200">


                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <input type="checkbox" name="all">
                                    </th>
                                    <th wire:click="doSort('id')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="id" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>
                                    <th wire:click="doSort('cod')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="cod" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>
                                    <th wire:click="doSort('nome')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="nome" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>
                                    <th class="px-2 py-1 text-left bg-gray-50">busca</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">lik|dislik</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">dt</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">views</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">keywds</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">comm uq/tot</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">duration</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">categ_id</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">NLP1</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">NLP2</th>
                                    <th class="px-2 py-1 text-left bg-gray-50">Gpt</th>




                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($videos as $video)
                                    <tr class="bg-white" wire:key="video-{{ $video->id }}">
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <input type="checkbox" wire:model="ids" name="checkb-{{ $video->id }}"
                                                value="{{ $video->id }}">
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->id }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a href="{{ $video->cod }}"
                                                class="underline hover:no-underline text-blue-600 hover:text-blue-900 visited:text-purple-600"
                                                target="_blank">
                                                {{ Str::of($video->cod)->chopStart('https://www.youtube.com/watch?v=') }}
                                            </a>

                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ Str::limit($video->nome, 45) }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->busca->slug ?? 'X' }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->likes }} | {{ $video->dislikes }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->dt }}
                                        </td>
                                        
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->views }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->keywords ? count(json_decode($video->keywords, true)) : 0 }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a
                                                href="{{ route('comentario', $video->id) }}">{{ $video->comentarios()->count() }}</a>
                                            / {{ $video->comments }}
                                        </td>


                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->duration }} s
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->categ_id }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->nlp1 }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->nlp2 }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $video->gpt }}
                                        </td>


                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a class="text-blue-700 font-semibold" 
                                                href="{{ route('toxic',$video) }}">Graf</a>
                                        </td>



                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="API('{{ $video->id }}')" href="#"
                                                class="text-green-700 font-semibold">Api</a>
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="Url('{{ $video->id }}')" href="#"
                                                class="text-green-700 font-semibold">Url</a>
                                            <div wire:loading wire:target="Url('{{ $video->id }}')">
                                                ...
                                            </div>
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="getComments('{{ $video->cod }}')" href="#"
                                                class="text-blue-700 font-semibold">Comm</a>
                                        </td>

                                        @if ($video->nome)
                                            <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <a wire:click.prevent="nlp1('{{ $video->id }}')" href="#"
                                                    class="text-green-700 font-semibold">NLP1</a>
                                            </td>
                                            <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <a wire:click.prevent="Gpt('{{ $video->id }}')" href="#"
                                                    class="text-green-700 font-semibold">Gpt</a>
                                            </td>
                                        @endif

                                        @if ($video->caption)
                                            <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <a wire:click.prevent="nlp2('{{ $video->id }}')" href="#"
                                                    class="text-green-700 font-semibold">NLP2</a>
                                            </td>

                                            <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <a wire:click.prevent="ranking('{{ $video->id }}')" href="#"
                                                    class="text-green-700 font-semibold">RnK</a>
                                            </td>


         <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <button class="text-yellow-700 font-semibold"
                                                wire:click.prevent="$dispatch('openModal', { component: 'manual', arguments: { canal: {{ $video->id }} }})">
                                                Man</button>
                                            </td>

 
                                            <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                <button class="text-yellow-700 font-semibold"
                                                wire:click.prevent="$dispatch('openModal', { component: 'rank', arguments: { video: {{ $video }} }})">
                                                RANK</button>
                                            </td>

                                        @endif

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <x-danger-button class=""
                                                wire:click="del('{{ $video->id }}')">del</x-danger-button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr class="bg-white">
                                        <td colspan="3"
                                            class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            No videos found.
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
