<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Warehouse Store Management System') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">

        <!-- Mobile overlay -->
        <div
            x-show="sidebarOpen"
            x-cloak
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <div class="lg:flex">

            <!-- Sidebar (mobile: fixed drawer, desktop: sticky) -->
            <aside
                class="fixed inset-y-0 left-0 z-50 w-72 transform bg-white border-r border-gray-200 transition lg:translate-x-0
                       lg:sticky lg:top-0 lg:h-screen lg:fixed lg:inset-auto lg:z-auto"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                @include('layouts.sidebar')
            </aside>

            <!-- Main content area -->
            <div class="flex-1 lg:min-h-screen">
                @include('layouts.topbar')

                <main class="p-4 sm:p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif
                    @yield('content')
                </main>
            </div>

        </div>
    </div>
    <footer class="mt-10 border-t text-center bg-white">
    <div class="max-w text-center px-4 py-3 text-md text-gray-600">
        <div>
            © {{ date('Y') }} {{ config('app.name') }} - Designed by Farhan Ellahi Owned by Nanosoft
        </div>
    </div>
</footer>

</body></html>
