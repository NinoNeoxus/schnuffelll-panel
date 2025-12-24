

<?php $__env->startSection('title', 'Servers'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <p class="text-sm text-slate-400">List of all servers running on the panel.</p>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="<?php echo e(route('admin.servers.create')); ?>" class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                Create Server
            </a>
        </div>
    </div>

    <div class="bg-slate-800 shadow-xl rounded-xl border border-slate-700 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-700">
            <thead class="bg-slate-900/50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Name</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Node</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Owner</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Status</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Resources</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700 bg-slate-800">
                <?php $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $server): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-slate-700/50 transition-colors">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0 bg-slate-700 rounded-lg flex items-center justify-center text-white font-bold">
                                <?php echo e(substr($server->name, 0, 2)); ?>

                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-white"><?php echo e($server->name); ?></div>
                                <div class="text-slate-400 text-xs"><?php echo e($server->uuidShort); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-300">
                         <div class="text-white"><?php echo e($server->node->name); ?></div>
                         <div class="text-slate-500 text-xs"><?php echo e($server->allocation->ip ?? 'Unknown'); ?>:<?php echo e($server->allocation->port ?? '0'); ?></div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-300">
                        <div class="text-white"><?php echo e($server->owner->name ?? 'Unknown'); ?></div>
                        <div class="text-slate-500 text-xs"><?php echo e($server->owner->email ?? 'N/A'); ?></div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-300">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                            <?php echo e($server->status == 'running' ? 'bg-emerald-500/10 text-emerald-400' : 
                               ($server->status == 'installing' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-500/10 text-slate-400')); ?>">
                            <?php echo e(ucfirst($server->status)); ?>

                        </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-xs text-slate-300">
                        <div><span class="text-slate-500">MEM:</span> <?php echo e($server->memory); ?>MB</div>
                        <div><span class="text-slate-500">DSK:</span> <?php echo e($server->disk); ?>MB</div>
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <a href="<?php echo e(route('admin.servers.show', $server)); ?>" class="text-blue-400 hover:text-blue-300">Manage</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-slate-700">
            <?php echo e($servers->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\panleee\schnuffelll\panel\resources\views/admin/servers/index.blade.php ENDPATH**/ ?>