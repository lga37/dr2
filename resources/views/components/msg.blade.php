@if (session('alert'))
    @php
        $alert = session('alert');
        $map = [
            'error' => 'border-red-200 bg-red-50 text-red-800',
            'warn' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
            'info' => 'border-blue-200 bg-blue-50 text-blue-800',
            'success' => 'border-green-200 bg-green-50 text-green-800',
        ];
        $cls = $map[$alert['type']] ?? 'border-gray-200 bg-gray-50 text-gray-800';

        $titles = [
            'error' => 'Erro',
            'warn' => 'Atenção',
            'info' => 'Informação',
            'success' => 'Sucesso',
        ];
        $title = $titles[$alert['type']] ?? 'Aviso';
    @endphp

    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)" x-show="show" x-transition
        class="mb-4 rounded-md border px-4 py-3 {{ $cls }}">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 text-lg leading-none">
                @switch($alert['type'])
                    @case('error')
                        &#x26A0;
                    @break

                    {{-- ⚠ --}}
                    @case('warn')
                        &#x26A0;
                    @break

                    {{-- ⚠ --}}
                    @case('info')
                        &#x2139;
                    @break

                    {{-- ℹ --}}
                    @case('success')
                        &#x2714;
                    @break

                    {{-- ✔ --}}

                    @default
                        &#x2139;
                @endswitch
            </div>
            <div class="flex-1">
                <div class="font-semibold">{{ $title }}</div>
                <div class="text-sm">{{ $alert['text'] }}</div>
            </div>
            <button type="button" class="opacity-70 hover:opacity-100" @click="show = false">✕</button>
        </div>
    </div>
@endif
