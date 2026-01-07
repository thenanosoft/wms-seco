<div class="h-full flex flex-col">
    <div class="px-5 py-4 border-b border-gray-200">
    <div class="text-base font-semibold leading-tight">
        Warehouse Store
        <div class="text-base font-semibold">Management System</div>
    </div>
    <div class="mt-1 text-xs text-gray-600">
        Offline LAN Inventory
    </div>
</div>


    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <div class="space-y-1">

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
               {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="w-2 h-2 rounded-full {{ request()->routeIs('dashboard') ? 'bg-white' : 'bg-gray-400' }}"></span>
                Dashboard
            </a>

            <div class="mt-4">
                <div class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Inventory</div>

                <a href="{{ route('purchases.index') }}"
                   class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Purchase (Inward)
                </a>

                <a href="{{ route('issues.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Issue (Outward)
                </a>

                <a href="{{ route('returns.index') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Returns
                </a>
            </div>

            @if(auth()->user()?->role === 'admin')
                <div class="mt-4">
                    <div class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Admin</div>

                    <a href="#"
                       class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Stock & Reports
                    </a>

                    <a href="{{ route('groups.index') }}"
   class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
    Groups
</a>

<a href="{{ route('items.index') }}"
   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
    Items
</a>

                    <a href="#"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Backup & Restore
                    </a>

                    <a href="#"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Users
                    </a>

                    <a href="#"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Logs
                    </a>
                </div>
            @endif
        </div>
    </nav>

    <div class="border-t border-gray-200 p-4">
        <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
        <div class="text-xs text-gray-600">
            Role: {{ auth()->user()->role }}
        </div>
    </div>
</div>
