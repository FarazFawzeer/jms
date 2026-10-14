<?php use App\Models\Customer; ?>



<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Suppliers', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Supplier List</h5>
                <p class="card-subtitle mb-0">All registered suppliers.</p>
            </div>
            <a href="<?php echo e(route('admin.suppliers.create')); ?>" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Supplier
            </a>
        </div>

        <div class="card-body">
            
            <form id="filterForm" action="<?php echo e(route('admin.suppliers.index')); ?>" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-4">
                    <input type="search" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Search name, code, mobile or city" autocomplete="off">
                </div>
                <div class="col-12 col-md-auto">
                    <select name="status" class="form-select">
                        <option value="active" <?php if($status === 'active'): echo 'selected'; endif; ?>>Active (<?php echo e($counts['active']); ?>)</option>
                        <option value="inactive" <?php if($status === 'inactive'): echo 'selected'; endif; ?>>Inactive (<?php echo e($counts['inactive']); ?>)</option>
                        <option value="all" <?php if($status === 'all'): echo 'selected'; endif; ?>>All (<?php echo e($counts['all']); ?>)</option>
                    </select>
                </div>
            </form>

            <div id="supplierTable">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Supplier Name</th>
                            <th scope="col">Mobile</th>
                            <th scope="col" class="d-none d-md-table-cell">Categories</th>
                            <th scope="col" class="d-none d-lg-table-cell">City / Address</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-muted">
                                    <?php echo e($s->code); ?>

                                    <?php if($s->user_code && $s->user_code !== $s->code): ?>
                                        <div class="fs-12"><?php echo e($s->user_code); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <h6 class="mb-0"><?php echo e($s->display_name); ?></h6>
                                    <?php if(trim((string) $s->Contract_Person)): ?>
                                        <small class="text-muted"><?php echo e(trim($s->Contract_Person)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap"><?php echo e($s->Tel1 ? Customer::formatPhone($s->Tel1) : '-'); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo e($categories[$s->code] ?? '-'); ?></td>
                                <td class="d-none d-lg-table-cell"><?php echo e($s->address ?: '-'); ?></td>
                                <td>
                                    <?php if($s->isActive()): ?>
                                        <span class="badge badge-soft-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-soft-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="<?php echo e(route('admin.suppliers.edit', $s->code)); ?>" class="btn btn-link btn-sm p-1 text-primary"
                                        title="Edit" aria-label="Edit">
                                        <iconify-icon icon="solar:pen-2-outline" class="align-middle fs-20"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn btn-link btn-sm p-1 <?php echo e($s->isActive() ? 'text-danger' : 'text-success'); ?> toggle-status"
                                        data-url="<?php echo e(route('admin.suppliers.status', $s->code)); ?>" data-active="<?php echo e($s->isActive() ? 1 : 0); ?>"
                                        data-name="<?php echo e($s->display_name); ?>" title="<?php echo e($s->isActive() ? 'Deactivate' : 'Activate'); ?>"
                                        aria-label="<?php echo e($s->isActive() ? 'Deactivate' : 'Activate'); ?>">
                                        <iconify-icon icon="<?php echo e($s->isActive() ? 'solar:forbidden-circle-outline' : 'solar:check-circle-outline'); ?>" class="align-middle fs-20"></iconify-icon>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <?php echo e(request('q') ? 'No suppliers found for "' . request('q') . '".' : 'No suppliers found.'); ?>

                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($suppliers->hasPages()): ?>
                <div class="mt-3">
                    <?php echo e($suppliers->onEachSide(1)->links('pagination::bootstrap-5')); ?>

                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('supplierTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('supplierTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load suppliers.', 'error'))
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
            form.status.addEventListener('change', () => load(filterUrl()));

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
                    title: active ? 'Deactivate supplier?' : 'Activate supplier?',
                    text: active ? button.dataset.name + ' will move to the inactive list. Old GRNs are not affected.' : button.dataset.name + ' will be active again.',
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
                            if (!data.success) throw new Error();
                            Swal.fire({ icon: 'success', title: 'Done', text: data.message, timer: 1300, showConfirmButton: false })
                                .then(() => location.reload());
                        })
                        .catch(() => Swal.fire('Error!', 'Something went wrong!', 'error'));
                });
            });
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Suppliers'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/suppliers/index.blade.php ENDPATH**/ ?>