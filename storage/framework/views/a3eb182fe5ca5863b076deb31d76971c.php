<?php
    use App\Models\AccountEntry;
    use App\Support\Division;
    $net = ($totals->income ?? 0) - ($totals->expense ?? 0);
?>



<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Income & Expenses', 'subtitle' => 'View'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Income &amp; Expense Entries</h5>
                <p class="card-subtitle mb-0">Money in and out, also posted to the cash book. Showing: <?php echo e(Division::label()); ?>.</p>
            </div>
            <a href="<?php echo e(route('admin.income-expenses.create')); ?>" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Entry
            </a>
        </div>

        <div class="card-body">
            
            <form id="filterForm" action="<?php echo e(route('admin.income-expenses.index')); ?>" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-3">
                    <input type="search" name="q" class="form-control" value="<?php echo e(request('q')); ?>"
                        placeholder="Search description / name / entry no." autocomplete="off">
                </div>
                <div class="col-6 col-md-auto">
                    <input type="date" name="from" class="form-control" value="<?php echo e($from->format('Y-m-d')); ?>" aria-label="From date">
                </div>
                <div class="col-6 col-md-auto">
                    <input type="date" name="to" class="form-control" value="<?php echo e($to->format('Y-m-d')); ?>" aria-label="To date">
                </div>
                <div class="col-6 col-md-auto">
                    <select name="type" class="form-select">
                        <option value="">Income &amp; Expense</option>
                        <?php $__currentLoopData = AccountEntry::DIRECTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php if(request('type') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <select name="account" class="form-select">
                        <option value="">All Accounts</option>
                        <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($a->code); ?>" <?php if(request('account') === $a->code): echo 'selected'; endif; ?>><?php echo e($a->display_name); ?></option>
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
                        <option value="active" <?php if($status === 'active'): echo 'selected'; endif; ?>>Active</option>
                        <option value="cancelled" <?php if($status === 'cancelled'): echo 'selected'; endif; ?>>Cancelled</option>
                        <option value="all" <?php if($status === 'all'): echo 'selected'; endif; ?>>All</option>
                    </select>
                </div>
            </form>

            <div id="entryTable">
            <p class="text-muted mb-2">
                <?php echo e(number_format($totals->entries ?? 0)); ?> entries &middot;
                Income <span class="text-success"><?php echo e(number_format($totals->income ?? 0, 2)); ?></span> &middot;
                Expense <span class="text-danger"><?php echo e(number_format($totals->expense ?? 0, 2)); ?></span> &middot;
                Net <span class="<?php echo e($net < 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format($net, 2)); ?></span>
            </p>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Account / Description</th>
                            <th scope="col" class="d-none d-md-table-cell">Paid To / From</th>
                            <th scope="col" class="d-none d-lg-table-cell">Mode</th>
                            <?php if($seesAll): ?>
                                <th scope="col" class="d-none d-md-table-cell">Division</th>
                            <?php endif; ?>
                            <th scope="col" class="text-end">Amount</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php [$dirText, $dirBadge] = $e->direction_label; ?>
                            <tr>
                                <td class="text-nowrap">
                                    <?php echo e($e->Ent_date?->format('d M Y')); ?>

                                    <br><small class="text-muted">No. <?php echo e($e->entry_no); ?></small>
                                </td>
                                <td>
                                    <h6 class="mb-0"><?php echo e($accounts->firstWhere('code', $e->account_code)?->display_name ?? $e->account_code); ?></h6>
                                    <small class="text-muted"><?php echo e($e->description_text); ?></small>
                                </td>
                                <td class="d-none d-md-table-cell"><?php echo e($e->party_name ?: '-'); ?></td>
                                <td class="d-none d-lg-table-cell"><?php echo e(AccountEntry::PAY_MODES[$e->pay_mode] ?? $e->pay_mode); ?></td>
                                <?php if($seesAll): ?>
                                    <td class="d-none d-md-table-cell"><?php echo e($e->division_name); ?></td>
                                <?php endif; ?>
                                <td class="text-end text-nowrap">
                                    <span class="badge <?php echo e($dirBadge); ?> me-1"><?php echo e($dirText); ?></span>
                                    <?php echo e(number_format($e->amount, 2)); ?>

                                </td>
                                <td>
                                    <?php if($e->isActive()): ?>
                                        <span class="badge badge-soft-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-soft-secondary">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="<?php echo e(route('admin.income-expenses.edit', $e->entry_no)); ?>" class="btn btn-link btn-sm p-1 text-primary"
                                        title="<?php echo e($e->isActive() ? 'Edit' : 'View'); ?>" aria-label="<?php echo e($e->isActive() ? 'Edit' : 'View'); ?>">
                                        <iconify-icon icon="<?php echo e($e->isActive() ? 'solar:pen-2-outline' : 'solar:eye-outline'); ?>" class="align-middle fs-20"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn btn-link btn-sm p-1 <?php echo e($e->isActive() ? 'text-danger' : 'text-success'); ?> toggle-status"
                                        data-url="<?php echo e(route('admin.income-expenses.status', $e->entry_no)); ?>" data-active="<?php echo e($e->isActive() ? 1 : 0); ?>"
                                        data-name="Entry <?php echo e($e->entry_no); ?> (<?php echo e(number_format($e->amount, 2)); ?>)" title="<?php echo e($e->isActive() ? 'Cancel' : 'Restore'); ?>"
                                        aria-label="<?php echo e($e->isActive() ? 'Cancel' : 'Restore'); ?>">
                                        <iconify-icon icon="<?php echo e($e->isActive() ? 'solar:forbidden-circle-outline' : 'solar:restart-outline'); ?>" class="align-middle fs-20"></iconify-icon>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e($seesAll ? 8 : 7); ?>" class="text-center text-muted py-4">
                                    <?php echo e(request('q') ? 'No entries found for "' . request('q') . '".' : 'No entries found for these dates.'); ?>

                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($entries->hasPages()): ?>
                <div class="mt-3">
                    <?php echo e($entries->onEachSide(1)->links('pagination::bootstrap-5')); ?>

                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('entryTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('entryTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load entries.', 'error'))
                    .finally(() => tableBox.style.opacity = 1);
            }

            function filterUrl() {
                return form.action + '?' + new URLSearchParams(new FormData(form));
            }

            form.q.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => load(filterUrl()), 400);
            });
            form.querySelectorAll('select, input[type="date"]').forEach(s => s.addEventListener('change', () => load(filterUrl())));

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
                    title: active ? 'Cancel entry?' : 'Restore entry?',
                    text: active ? button.dataset.name + ' will be taken out of the totals and the cash book.' : button.dataset.name + ' will be added back to the totals and the cash book.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: active ? '#d33' : '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: active ? 'Yes, cancel it' : 'Yes, restore',
                    cancelButtonText: 'No'
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

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Income & Expenses'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/income-expenses/index.blade.php ENDPATH**/ ?>