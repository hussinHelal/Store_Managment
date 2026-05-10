<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    @include('components.head')
    <body>
        <header>
            @include('components.nav')
        </header>

        {{-- sidebar on the right (RTL), main on the left --}}
        <div class="d-flex">
            @include('components.sidebar')
            <main class="flex-grow-1 p-4">
                @yield('main')
                @yield('content')
            </main>

        </div>

        @include('components.foot')
    </body>
</html>