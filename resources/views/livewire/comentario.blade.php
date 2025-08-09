<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Comentarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-12xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">


                    <div class="flex items-center justify-around mb-3">
                        <div class="flex items-center space-x-4">
                            <x-input-label>Search</x-input-label>
                            <x-text-input wire:model.live.debounce.500ms="search" name="q" />
                        </div>
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

                        <div class="flex items-center">
                            <x-input-label>Video</x-input-label>
                            <select wire:model.live="video_id" class="rounded">
                                @foreach ($videos as $video)
                                    <option value="{{ $video->id }}">{{ Str::limit($video->nome, 18) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="">{{ $comentarios->links() }}</div>
                    </div>



                    <div class="min-w-full align-middle">
                        <table class="min-w-full border divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th wire:click="doSort('id')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="id" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>

                                    <th wire:click="doSort('user')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="user" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>

                                    
                                    <th wire:click="doSort('video_id')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="video_id" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>
                                    <th wire:click="doSort('dt')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="dt" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>


                                   
                                    <th class="px-2 py-1 text-left bg-gray-50">txt</th>

                                    <th class="px-2 py-1 text-left bg-gray-50">video</th>
                                    <th wire:click="doSort('likes')" class="px-2 py-1 text-left bg-gray-50">
                                        <x-datatable-item columnName="likes" :sortColumn="$sortColumn" :sortDirection="$sortDirection" />
                                    </th>


                                    <th class="px-2 py-1 text-left bg-gray-50">tox</th>



                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($comentarios as $comentario)
                                    <tr class="bg-white">
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $comentario->id }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ Str::limit($comentario->user, 8) }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $comentario->video->id }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $comentario->dt }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">

                                            {{ Str::limit($comentario->texto, 80) }}
                                        </td>
                                     

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a href="{{ $comentario->video->cod }}"
                                                class="underline hover:no-underline text-blue-600 hover:text-blue-900 visited:text-purple-600"
                                                target="_blank">
                                                {{ Str::limit($comentario->video->nome, 30) }}
                                            </a>
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $comentario->likes }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $comentario->tox }}
                                        </td>
                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="setTox({{ $comentario->id }})"
                                                class="text-blue-700 font-semibold" href="#">Tox</a>
                                        </td>

                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <x-danger-button class=""
                                                wire:click.prevent="del('{{ $comentario->id }}')">del</x-danger-button>
                                        </td>
                                        <td class="px-2 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $comentario->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white">
                                        <td colspan="3"
                                            class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            No comentarios found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
