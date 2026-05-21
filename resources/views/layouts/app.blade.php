<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" data-bs-theme="light">
    @include('components.head')
    <body>
        <header>
            @include('components.nav')
        </header>

        <div class="d-flex">
            @include('components.sidebar')
            <main class="flex-grow-1 p-4">
                @yield('main')
                @yield('content')
            </main>

        </div>

        @include('components.foot')
        
        @stack('scripts')
        
    </body>
</html>