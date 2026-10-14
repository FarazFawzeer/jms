<?php use App\Support\Division; ?>



<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Products', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Product List</h5>
                <p class="card-subtitle mb-0">Every piece with its barcode. Showing: <?php echo e(Division::label()); ?>.</p>
            </div>
            <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Product
            </a>
        </div>

        <div class="card-body">
            
            <form id="filterForm" action="<?php echo e(route('admin.products.index')); ?>" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-3">
                    <input type="search" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Scan or search barcode / name" autocomplete="off">
                </div>
                <div class="col-6 col-md-auto">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->code); ?>" <?php if(request('category') === $c->code): echo 'selected'; endif; ?>><?php echo e($c->display_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <select name="unit" class="form-select">
                        <option value="">All Karats</option>
                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($code); ?>" <?php if(request('unit') === (string) $code): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php if($seesAll): ?>
                    <div class="col-6 col-md-auto">
                        <select name="division" class="form-select">
                            <option value="">All Divisions</option>
                            <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($d->code); ?>" <?php if(request('division') === $d->code): echo 'selected'; endif; ?>><?php echo e($d->display_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-6 col-md-auto">
                    <select name="status" class="form-select">
                        <option value="stock" <?php if($status === 'stock'): echo 'selected'; endif; ?>>In Stock</option>
                        <option value="sold" <?php if($status === 'sold'): echo 'selected'; endif; ?>>Sold</option>
                        <option value="inactive" <?php if($status === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                        <option value="all" <?php if($status === 'all'): echo 'selected'; endif; ?>>All</option>
                    </select>
                </div>
            </form>

            <div id="productTable">
            <p class="text-muted mb-2">
                <?php echo e(number_format($totals->pieces ?? 0)); ?> items &middot; <?php echo e(number_format($totals->weight ?? 0, 3)); ?> g
            </p>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Barcode</th>
                            <th scope="col">Item</th>
                            <th scope="col" class="d-none d-md-table-cell">Karat</th>
                            <th scope="col" class="text-end">Weight (g)</th>
                            <th scope="col" class="d-none d-lg-table-cell text-end">Stone (g)</th>
                            <?php if($seesAll): ?>
                                <th scope="col" class="d-none d-md-table-cell">Division</th>
                            <?php endif; ?>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php [$soldText, $soldBadge] = $p->sold_label; ?>
                            <tr>
                                <td class="text-nowrap"><?php echo e($p->barcode_no); ?></td>
                                <td>
                                    <h6 class="mb-0"><?php echo e($p->display_name); ?></h6>
                                    <small class="text-muted"><?php echo e($categories->firstWhere('code', $p->category_code)?->display_name ?? $p->category_code); ?></small>
                                </td>
                                <td class="d-none d-md-table-cell"><?php echo e($units[trim((string) $p->unit_code)] ?? '-'); ?></td>
                                <td class="text-end"><?php echo e(number_format($p->item_size, 3)); ?></td>
                                <td class="d-none d-lg-table-cell text-end"><?php echo e($p->Stone_weight ? number_format($p->Stone_weight, 3) : '-'); ?></td>
                                <?php if($seesAll): ?>
                                    <td class="d-none d-md-table-cell"><?php echo e($p->division_name); ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if(! $p->isActive()): ?>
                                        <span class="badge badge-soft-danger">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge <?php echo e($soldBadge); ?>"><?php echo e($soldText); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="<?php echo e(route('admin.products.edit', $p->code)); ?>" class="btn btn-link btn-sm p-1 text-primary"
                                        title="<?php echo e($p->isEditable() ? 'Edit' : 'View'); ?>" aria-label="<?php echo e($p->isEditable() ? 'Edit' : 'View'); ?>">
                                        <iconify-icon icon="<?php echo e($p->isEditable() ? 'solar:pen-2-outline' : 'solar:eye-outline'); ?>" class="align-middle fs-20"></iconify-icon>
                                    </a>
                                    <?php if($p->inStock()): ?>
                                        <button type="button" class="btn btn-link btn-sm p-1 <?php echo e($p->isActive() ? 'text-danger' : 'text-success'); ?> toggle-status"
                                            data-url="<?php echo e(route('admin.products.status', $p->code)); ?>" data-active="<?php echo e($p->isActive() ? 1 : 0); ?>"
                                            data-name="<?php echo e($p->display_name); ?> (<?php echo e($p->barcode_no); ?>)" title="<?php echo e($p->isActive() ? 'Deactivate' : 'Activate'); ?>"
                                            aria-label="<?php echo e($p->isActive() ? 'Deactivate' : 'Activate'); ?>">
                                            <iconify-icon icon="<?php echo e($p->isActive() ? 'solar:forbidden-circle-outline' : 'solar:check-circle-outline'); ?>" class="align-middle fs-20"></iconify-icon>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e($seesAll ? 8 : 7); ?>" class="text-center text-muted py-4">
                                    <?php echo e(request('q') ? 'No items found for "' . request('q') . '".' : 'No items found.'); ?>

                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($products->hasPages()): ?>
                <div class="mt-3">
                    <?php echo e($products->onEachSide(1)->links('pagination::bootstrap-5')); ?>

                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('productTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('productTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load items.', 'error'))
                    .finally(() => tableBox.style.opacity = 1);
            }

            function filterUrl() {
                return form.action + '?' + new URLSearchParams(new FormData(form));
            }

            // filter automatically while typing / choosing
            form.q.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => load(filterUrl()), 400);
            });
            form.querySelectorAll('select').forEach(s => s.addEventListener('change', () => load(filterUrl())));

            tableBox.addEventListener('click', function(e) {
                const pageLink = e.target.closest('.pagination a');
                if (pageLink) {
                    e.preventDefault();
                    load(pageLink.href);
                    return;
                }

                const button = e.target.closest('.toggle-status');
                if (!button) return;

                const active = button.dataset.active === '1';
                Swal.fire({
                    title: active ? 'Deactivate item?' : 'Activate item?',
                    text: active ? button.dataset.name + ' will be removed from the stock list.' : button.dataset.name + ' will be back in stock.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: active ? '#d33' : '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: active ? 'Yes, deactivate' : 'Yes, activate'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(button.dataset.url, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>",
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) return Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                            Swal.fire({ icon: 'success', title: 'Done', text: data.message, timer: 1300, showConfirmButton: false })
                                .then(() => load(location.href));
                        })
                        .catch(() => Swal.fire('Error!', 'Something went wrong!', 'error'));
                });
            });
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Products'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/products/index.blade.php ENDPATH**/ ?>