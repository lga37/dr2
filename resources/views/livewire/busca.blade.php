<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Buscas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-hidden overflow-x-auto bg-white border-b border-gray-200">

                    @if (session()->has('success'))
                        <div class="bg-green-200 p-3 rounded text-green-800" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="min-w-full align-middle">
                        <form method="POST" class="flex items-center mb-3" wire:submit='add'>
                            <x-input-label for="busca" class="mr-1" :value="__('Query')" />
                            <x-text-input id="busca" wire:model="query" class="mt-1 w-40" />

                            <x-primary-button class="ms-3">
                                {{ __('Add') }}
                            </x-primary-button>
                        </form>
                        @error('query')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <x-input-error :messages="$errors->get('busca')" class="mt-2" />

                        <table class="min-w-full border divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <input type="checkbox" name="all">
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">id</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">slug</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">videos</span>
                                    </th>
                                    <th class="px-6 py-3 text-left bg-gray-50">
                                        <span
                                            class="text-xs font-medium leading-4 tracking-wider text-gray-500 uppercase">canais</span>
                                    </th>

                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($buscas as $busca)
                                    <tr class="bg-white">

                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <input type="checkbox" wire:model="ids" name="checkb-{{ $busca->id }}"
                                                value="{{ $busca->id }}">
                                        </td>



                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $busca->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $busca->slug }}
                                        </td>

                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $busca->videos()->count() }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $busca->canals()->count() }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $busca->created_at->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $busca->updated_at->diffForHumans() }}
                                        </td>

                                        <td class="px-3 py-1 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <a wire:click.prevent="Bot('{{ $busca->id }}')" href="#"
                                                class="text-green-700 font-semibold">Bot</a>




                                            <div wire:loading wire:target="Bot('{{ $busca->id }}')">
                                                loading ...
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <x-danger-button class=""
                                                wire:click="del('{{ $busca->id }}')">del</x-danger-button>
                                        </td>
                                    </tr>
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
                    </div>


                    <div wire:stream="out">{{ $out }}</div>




                    <x-secondary-button wire:click="craw" class="mt-3">
                        {{ __('Crawling Youtube') }}
                    </x-secondary-button>

                    <div class="w-full bg-gray-300" wire:stream="result">
                        {{ $content ?? '' }}
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
