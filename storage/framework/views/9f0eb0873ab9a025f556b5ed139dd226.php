<header class="sticky top-0 z-30 bg-white border-b border-gray-200">
    <div class="flex items-center gap-3 px-4 sm:px-6 h-14">

        <!-- Mobile menu button -->
        <button
            type="button"
            class="lg:hidden inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium hover:bg-gray-50"
            @click="sidebarOpen = true"
        >
            Menu
        </button>

        <div class="flex-1">
            <div class="text-sm font-semibold text-gray-900">
                Dashboard
            </div>
            <div class="text-xs text-gray-600">
                <?php echo e(now()->format('D, d M Y')); ?>

            </div>
        </div>

        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button
                type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
            >
                Logout
            </button>
        </form>
    </div>
</header>
<?php /**PATH C:\laragon\www\wms\resources\views/layouts/topbar.blade.php ENDPATH**/ ?>