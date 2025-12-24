

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-xl bg-slate-800 p-6 shadow-xl border border-slate-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-blue-500/10 p-3">
                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.375a17.25 17.25 0 01-3.468 5.688 6 12 12 0 11-5.688-3.468 17.25 17.25 0 015.688 3.468zm0 0V21m0-18v3.375" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-slate-400">Total Servers</dt>
                        <dd class="mt-1 text-2xl font-bold tracking-tight text-white">0</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-slate-800 p-6 shadow-xl border border-slate-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-emerald-500/10 p-3">
                    <svg class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-slate-400">Online Servers</dt>
                        <dd class="mt-1 text-2xl font-bold tracking-tight text-white">0</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="overflow-hidden rounded-xl bg-slate-800 p-6 shadow-xl border border-slate-700">
             <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-purple-500/10 p-3">
                    <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-slate-400">Total Nodes</dt>
                        <dd class="mt-1 text-2xl font-bold tracking-tight text-white">0</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div class="mt-8 rounded-xl bg-slate-800 p-8 text-center shadow-xl border border-slate-700">
        <svg class="mx-auto h-12 w-12 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.375a17.25 17.25 0 01-3.468 5.688 6 12 12 0 11-5.688-3.468 17.25 17.25 0 015.688 3.468zm0 0V21m0-18v3.375" />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-white">No servers deployed</h3>
        <p class="mt-1 text-sm text-slate-400">Get started by creating a new server instance.</p>
        <div class="mt-6">
            <button type="button" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Deploy Server
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\panleee\schnuffelll\panel\resources\views/dashboard.blade.php ENDPATH**/ ?>