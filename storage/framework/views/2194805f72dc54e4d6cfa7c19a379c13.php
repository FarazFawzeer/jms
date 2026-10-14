<div class="app-sidebar">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="<?php echo e(route('any', 'index')); ?>" class="logo-dark">
            <img src="/images/jms.png" class="logo-sm" alt="logo sm">
            <img src="/images/jms.png" class="logo-lg" alt="logo dark" style="width: 150px; height: 75px;">
        </a>

        <a href="<?php echo e(route('any', 'index')); ?>" class="logo-light">
            <img src="/images/jms.png" class="logo-sm" alt="logo sm">
            <img src="/images/jms.png" class="logo-lg" alt="logo light" style="width: 150px; height: 75px;">
        </a>
    </div>

    <div class="scrollbar" data-simplebar>

        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Menu...</li>

            

            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.users.index')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:user-circle-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Admin </span>
                </a>
            </li>

            
            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.customers.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.customers.index')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:users-group-rounded-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Customers </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.suppliers.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.suppliers.index')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:delivery-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Suppliers </span>
                </a>
            </li>

            
            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.products.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.products.index')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:tag-price-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Products </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.categories.index')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:widget-4-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Categories </span>
                </a>
            </li>
            <?php if(auth()->user()?->canManageSystem()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('admin.company.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('admin.company.index')); ?>">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:shop-2-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Company </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('admin.divisions.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('admin.divisions.index')); ?>">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:buildings-2-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Divisions </span>
                    </a>
                </li>
            <?php endif; ?>

            
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.income-expenses.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.income-expenses.index')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:wallet-money-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Income &amp; Expenses </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('admin.account-types.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.account-types.index')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:bill-list-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Account Types </span>
                </a>
            </li>


            <li class="nav-item">
                <a class="nav-link" href="<?php echo e(route('admin.profile.edit')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:widget-2-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Profile </span>

                </a>
            </li>

            
        </ul>
    </div>
</div>
<?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>