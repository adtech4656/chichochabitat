<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#090C10">
        <meta name="color-scheme" content="dark">

        <title inertia>{{ config('app.name', 'ChiChoc-Habitat') }}</title>

        <link rel="preconnect" href="https://images.unsplash.com" crossorigin>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="bg-void text-heading antialiased">
        @inertia
    </body>
</html>
