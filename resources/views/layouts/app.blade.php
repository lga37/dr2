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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <style>
        tr:has(input[type="checkbox"]:checked) {
            background-color: #e0e0e0;
            /* Or any color you prefer */
        }


        .grafico {
            display: flex;
            align-items: flex-end;
            height: 300px;
            border-left: 2px solid #444;
            border-bottom: 2px solid #444;
            padding: 10px;
            gap: 5px;
            overflow-x: auto;
            max-width: 100%;
            box-sizing: border-box;
        }

        .barra {
            width: 40px;
            background-color: steelblue;
            text-align: center;
            color: white;
            font-size: 11px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 2px;
            box-sizing: border-box;
            position: relative;
        }

        .legenda {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-size: 10px;
            text-align: center;
            margin-top: 5px;
        }

        .barra-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 50px;
            /* importante para não colar */
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
            {{ $slot }}
        </main>
    </div>
</body>


@livewire('wire-elements-modal')



</html>