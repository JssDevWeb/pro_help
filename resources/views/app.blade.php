<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Forzar tema claro por defecto para mejor apariencia --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "light" }}';
                
                // Solo aplicar tema oscuro si está explícitamente configurado
                if (appearance === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        {{-- Estilo para fondo claro y limpio --}}
        <style>
            html {
                background-color: #fafafa;
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        
        <meta name="base-url" content="{{ url('/') }}">
        <link rel="icon" href="{{ url('/favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ url('/favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ url('/apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
