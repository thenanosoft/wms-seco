<div class="h-full flex flex-col">
    <div class="px-5 py-4 border-b border-gray-200">
        <div class="text-base font-semibold leading-tight">{{ config('app.name') }}</div>
        <div class="mt-1 text-xs text-gray-600">
            Offline LAN Inventory
        </div>
    </div>

    @php
        $isAdmin = auth()->user()?->role === 'admin';
    @endphp

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <div class="space-y-1">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
               {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 12l9-9 9 9" />
                    <path d="M9 21V9h6v12" />
                </svg>
                Dashboard
            </a>

            {{-- Inventory --}}
            <div class="mt-4">
                <div class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Inventory</div>

                <a href="{{ route('purchases.index') }}"
                   class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                   {{ request()->routeIs('purchases.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 2v20" />
                        <path d="M5 9l7-7 7 7" />
                    </svg>
                    Purchase (Inward)
                </a>

                <a href="{{ route('issues.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                   {{ request()->routeIs('issues.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 22V2" />
                        <path d="M19 15l-7 7-7-7" />
                    </svg>
                    Issue (Outward)
                </a>

                <a href="{{ route('returns.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                   {{ request()->routeIs('returns.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M7 7h10v10H7z" />
                        <path d="M7 12h10" />
                        <path d="M12 7v10" />
                    </svg>
                    Returns
                </a>
            </div>

            {{-- Admin --}}
            @if($isAdmin)
                <div class="mt-4">
                    <div class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Admin</div>

                    <a href="{{ route('stock.index') }}"
                       class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('stock.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 19V5" />
                            <path d="M4 19h16" />
                            <path d="M8 15V9" />
                            <path d="M12 17V7" />
                            <path d="M16 13v-2" />
                        </svg>
                        Stock & Reports
                    </a>

                    <a href="{{ route('pending_prices.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('pending_prices.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 2v20" />
                            <path d="M4 6h16" />
                            <path d="M4 12h16" />
                            <path d="M4 18h16" />
                        </svg>
                        Pending Prices
                    </a>

                    <a href="{{ route('reports.valuation.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('reports.valuation.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M3 3v18h18" />
                            <path d="M7 14l3-3 4 4 5-6" />
                        </svg>
                        FIFO Valuation
                    </a>

                    <a href="{{ route('reports.balance.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('reports.balance.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 19V5" />
                            <path d="M4 19h16" />
                            <path d="M8 15V9" />
                            <path d="M12 17V7" />
                            <path d="M16 13v-2" />
                        </svg>
                        Balance Report
                    </a>

                    <a href="{{ route('purchases.items.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('purchases.items.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M8 6h13" />
                            <path d="M8 12h13" />
                            <path d="M8 18h13" />
                            <path d="M3 6h1" />
                            <path d="M3 12h1" />
                            <path d="M3 18h1" />
                        </svg>
                        Purchase Items List
                    </a>

                    <a href="{{ route('groups.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('groups.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 7h16" />
                            <path d="M4 12h16" />
                            <path d="M4 17h16" />
                        </svg>
                        Groups
                    </a>

                    <a href="{{ route('items.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('items.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20 7l-8-4-8 4v10l8 4 8-4V7z" />
                            <path d="M12 3v18" />
                        </svg>
                        Items
                    </a>

                    <a href="{{ route('settings.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('settings.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.7 3-0.2-.1a1.8 1.8 0 0 0-2.1.3l-.1.1-3.4-2a1.8 1.8 0 0 0-1.7 0l-3.4 2-.1-.1a1.8 1.8 0 0 0-2.1-.3l-.2.1-1.7-3 .1-.1a1.7 1.7 0 0 0 .3-1.9v-.1a1.8 1.8 0 0 0-1.2-1.3H2v-4h.2a1.8 1.8 0 0 0 1.2-1.3v-.1a1.7 1.7 0 0 0-.3-1.9l-.1-.1 1.7-3 .2.1a1.8 1.8 0 0 0 2.1-.3l.1-.1 3.4 2a1.8 1.8 0 0 0 1.7 0l3.4-2 .1.1a1.8 1.8 0 0 0 2.1.3l.2-.1 1.7 3-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.8 1.8 0 0 0 1.2 1.3H22v4h-.2a1.8 1.8 0 0 0-1.2 1.3z" />
                        </svg>
                        Settings
                    </a>

                    <a href="{{ route('backup.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                       {{ request()->routeIs('backup.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5 flex-none opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 6V3" />
                            <path d="M8 3h8" />
                            <path d="M6 6h12" />
                            <path d="M7 6v15h10V6" />
                            <path d="M9 10h6" />
                        </svg>
                        Backup & Restore
                    </a>
                </div>
            @endif

        </div>
    </nav>

    <div class="border-t border-gray-200 p-4">
        <a href="{{ route('profile.index') }}" class="block">
            <div class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 transition-colors bg-gray-200">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white flex-shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 truncate capitalize">{{ auth()->user()->role }}</div>
                </div>
            </div>
        </a>
    </div>
</div>
