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


        <main>
            @php
                $authRoutes = ['login', 'register', 'password.*'];
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
    @stack('scripts')


<footer class="py-3 print:hidden">
    <div class="mx-auto max-w-7xl px-4">

        <div
            class="mt-5 border-t border-gray-700 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- lado esquerdo --}}
            <div class="text-sm text-center sm:text-left">
                ©2026 tesedoutorado.com.br - UNIRIO - RJ - Brasil
            </div>

            {{-- lado direito --}}
            <div class="text-xs font-semibold text-center sm:text-right">

                <div class="flex flex-wrap justify-center sm:justify-end items-center gap-2 mb-1">
                    <span>Ingredientes:</span>

                    <span class="bg-green-400 text-black px-2 py-0.5 rounded">
                        PHP
                    </span>

                    <span class="bg-yellow-400 text-black px-2 py-0.5 rounded">
                        Laravel
                    </span>

                    <span class="bg-blue-400 text-black px-2 py-0.5 rounded">
                        🐧 Linux
                    </span>
                </div>

                <div class="italic">
                    Cozinhado com ❤️ e I.A. por
                    <a class="text-sky-300 no-underline hover:underline"
                        href="https://www.linkedin.com/in/gustavoalmeidapro/"
                        target="_blank">
                        LGA
                    </a>
                </div>

            </div>

        </div>

    </div>
</footer>


</body>


</html>

@push('scripts')
    <script></script>
@endpush
