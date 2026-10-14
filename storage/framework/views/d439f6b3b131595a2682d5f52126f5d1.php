<?php $__env->startSection('content'); ?>

<?php echo $__env->make('layouts.partials.page-title', ['title' => 'JMS', 'subtitle' => 'Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $user = auth()->user();
    $canManage = $user?->canManageSystem();

    // Quick links to the finished modules. 'admin' => true means only Overall-division admins see it.
    $modules = [
        ['route' => 'admin.customers.index',       'title' => 'Customers',          'text' => 'Register and manage customers',   'icon' => 'solar:users-group-rounded-outline', 'color' => 'primary'],
        ['route' => 'admin.suppliers.index',       'title' => 'Suppliers',          'text' => 'Register and manage suppliers',   'icon' => 'solar:delivery-outline',            'color' => 'success'],
        ['route' => 'admin.products.index',        'title' => 'Products',           'text' => 'Items held in stock',             'icon' => 'solar:tag-price-outline',           'color' => 'warning'],
        ['route' => 'admin.categories.index',      'title' => 'Categories',         'text' => 'Item categories',                 'icon' => 'solar:widget-4-outline',            'color' => 'info'],
        ['route' => 'admin.income-expenses.index', 'title' => 'Income & Expenses',  'text' => 'Record income and expenses',      'icon' => 'solar:wallet-money-outline',        'color' => 'success'],
        ['route' => 'admin.account-types.index',   'title' => 'Account Types',      'text' => 'Income and expense heads',        'icon' => 'solar:bill-list-outline',           'color' => 'secondary'],
        ['route' => 'admin.company.index',         'title' => 'Company',            'text' => 'Company details',                 'icon' => 'solar:shop-2-outline',              'color' => 'danger',  'admin' => true],
        ['route' => 'admin.divisions.index',       'title' => 'Divisions',          'text' => 'Branches and stock locations',    'icon' => 'solar:buildings-2-outline',         'color' => 'primary', 'admin' => true],
        ['route' => 'admin.users.index',           'title' => 'Users',              'text' => 'System users and access',         'icon' => 'solar:user-circle-outline',         'color' => 'dark',    'admin' => true],
    ];

    $modules = array_filter($modules, fn ($m) => empty($m['admin']) || $canManage);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-md bg-primary bg-opacity-10 rounded-circle flex-shrink-0">
                        <iconify-icon icon="solar:crown-outline" class="fs-32 text-primary avatar-title"></iconify-icon>
                    </div>
                    <div>
                        <h4 class="mb-1">Welcome, <?php echo e($user?->name); ?></h4>
                        <p class="text-muted mb-0">
                            Jewellery Management System
                            <span class="badge badge-soft-primary ms-1"><?php echo e($user?->division_name); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-6 col-xl-4">
            <a href="<?php echo e(route($module['route'])); ?>" class="card text-reset">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-md bg-<?php echo e($module['color']); ?> bg-opacity-10 rounded-circle flex-shrink-0">
                            <iconify-icon icon="<?php echo e($module['icon']); ?>" class="fs-32 text-<?php echo e($module['color']); ?> avatar-title"></iconify-icon>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h5 class="text-dark mb-1"><?php echo e($module['title']); ?></h5>
                            <p class="text-muted mb-0 text-truncate"><?php echo e($module['text']); ?></p>
                        </div>
                        <i class="bx bx-right-arrow-alt fs-20 text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-md bg-info bg-opacity-10 rounded-circle flex-shrink-0">
                        <iconify-icon icon="solar:chart-2-outline" class="fs-32 text-info avatar-title"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="mb-1">Analytics dashboard coming soon</h5>
                        <p class="text-muted mb-0">Sales, stock and income summaries for each division will appear here once those modules are complete.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/index.blade.php ENDPATH**/ ?>