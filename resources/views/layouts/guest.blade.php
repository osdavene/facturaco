<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FacturaCO') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#0b0f1a] text-slate-200 font-sans antialiased min-h-screen flex flex-col justify-center items-center p-6">
        <div class="w-full sm:max-w-md">
            <div class="flex justify-center mb-6">
                <a href="/" class="inline-flex items-center gap-3">
                    <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center font-display font-black text-black text-xl">FC</div>
                    <span class="font-display font-black text-2xl text-white">Factura<span class="text-amber-500">CO</span></span>
                </a>
            </div>

            <div class="card p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
