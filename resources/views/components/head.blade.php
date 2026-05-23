{{-- head start --}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name','Bebo') }} @yield('title')</title>
    <script>
           (() => {
               const savedTheme = localStorage.getItem('theme');
               const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
               const theme = savedTheme || (prefersDark ? 'dark' : 'light');
               document.documentElement.setAttribute('data-bs-theme', theme);
           })();
       </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- head end --}}
