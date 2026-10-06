<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" data-bs-theme="light">
    @include('components.head')
    <body class="guest-layout">
        <main class="guest-main min-vh-100 d-flex align-items-center justify-content-center p-3">
            @yield('content')
        </main>

        @include('components.foot')
        @stack('scripts')
    </body>
</html>