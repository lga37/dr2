<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/wordcloud@1.2.2/src/wordcloud2.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireScripts


    <style>
        .wcTextCloud {
            width: 100%;
            min-height: 180px;
            padding: 10px 12px;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 10px 14px;
            /* espaço entre palavras */
            line-height: 1;
            overflow: hidden;
        }

        .wcWord {
            font-weight: 700;
            letter-spacing: -0.02em;
            user-select: none;
            white-space: nowrap;
        }
    </style>

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            @php
                // Todas as telas de autenticação que usam o layout compacto
                $authRoutes = ['login', 'register', 'password.*'];
                // Se quiser incluir verificação de email etc.:  ['login','register','password.*','verification.*']
            @endphp
            @if (request()->routeIs($authRoutes))
                <div class="flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 ">
                    <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
                        {{ $slot }}
                    </div>
                </div>
            @else
                {{ $slot }}
            @endif
        </main>
    </div>
    {{-- @livewire('wire-elements-modal') --}}

    @stack('scripts')
</body>


</html>

@push('scripts')
    <script>
        // document.addEventListener('alpine:init', () => {
        //     Alpine.data('collapsible', (storageKey, defOpen = true) => ({
        //         open: JSON.parse(localStorage.getItem(storageKey) ?? JSON.stringify(defOpen)),
        //         toggle() {
        //             this.open = !this.open;
        //             localStorage.setItem(storageKey, JSON.stringify(this.open));
        //         }
        //     }));
        // });
    </script>
@endpush
