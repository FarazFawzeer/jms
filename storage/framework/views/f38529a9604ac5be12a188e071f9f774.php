<?php
    use App\Models\AccountEntry;
    use App\Support\Division;
    $isEdit = $entry->exists;
    $locked = $isEdit && ! $entry->isActive();
?>



<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Income & Expenses', 'subtitle' => $isEdit ? 'Edit' : 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0"><?php echo e($isEdit ? ($locked ? 'Entry Details' : 'Edit Entry') : 'New Income / Expense'); ?></h5>
            <span class="badge badge-soft-primary fs-13">
                <?php echo e($isEdit ? 'Entry No: ' . $entry->entry_no : 'Entry no. is given on save'); ?>

            </span>
        </div>

        <div class="card-body">
            <?php if($locked): ?>
                <div class="alert alert-info">This entry is cancelled. Restore it from the list to change it.</div>
            <?php endif; ?>

            <form id="entryForm" novalidate
                action="<?php echo e($isEdit ? route('admin.income-expenses.update', $entry->entry_no) : route('admin.income-expenses.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                <fieldset <?php if($locked): echo 'disabled'; endif; ?>>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Entry Type <span class="text-danger">*</span></label>
                        <?php $__currentLoopData = AccountEntry::DIRECTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type<?php echo e($key); ?>" value="<?php echo e($key); ?>"
                                    <?php if($entry->direction === $key): echo 'checked'; endif; ?>>
                                <label class="form-check-label" for="type<?php echo e($key); ?>"><?php echo e($label); ?> (<?php echo e($key === AccountEntry::INCOME ? 'money in' : 'money out'); ?>)</label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="division" class="form-label">Division <?php if($seesAll && ! $isEdit): ?><span class="text-danger">*</span><?php endif; ?></label>
                        <?php if($seesAll && ! $isEdit): ?>
                            <select id="division" name="division" class="form-select" required>
                                <option value="">Select Division</option>
                                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($d->code); ?>" <?php if($entry->division_code === $d->code): echo 'selected'; endif; ?>><?php echo e($d->display_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php else: ?>
                            <input type="text" id="division" class="form-control" readonly
                                value="<?php echo e($isEdit ? $entry->division_name : Division::label()); ?>">
                        <?php endif; ?>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" id="date" name="date" class="form-control" max="<?php echo e(now()->format('Y-m-d')); ?>"
                            value="<?php echo e($entry->Ent_date?->format('Y-m-d')); ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="account" class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select id="account" name="account" class="form-select" required>
                            <option value="">Select Account</option>
                            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($a->code); ?>" data-category="<?php echo e($a->category); ?>" <?php if($entry->account_code === $a->code): echo 'selected'; endif; ?>><?php echo e($a->display_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" inputmode="decimal"
                            value="<?php echo e($isEdit ? $entry->amount : ''); ?>" placeholder="Ex: 5000.00" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pay_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select id="pay_mode" name="pay_mode" class="form-select" required>
                            <?php $__currentLoopData = AccountEntry::PAY_MODES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php if($entry->pay_mode === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="party" class="form-label">Paid To / Received From <span class="text-danger">*</span></label>
                        <select id="party" name="party" class="form-select" required>
                            <?php $__currentLoopData = $parties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e(trim($p->Cust_Sys_Code)); ?>" <?php if($entry->party_code === trim($p->Cust_Sys_Code)): echo 'selected'; endif; ?>><?php echo e(trim($p->Cust_Name)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="form-text">Leave as CASH when it is not for a customer.</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" id="description" name="description" class="form-control" maxlength="50"
                            value="<?php echo e($entry->description_text); ?>" placeholder="Ex: ELECTRICITY BILL - SEPTEMBER" autocomplete="off" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                </fieldset>

                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('admin.income-expenses.index')); ?>" class="btn btn-light"><?php echo e($locked ? 'Back' : 'Cancel'); ?></a>
                    <?php if (! ($locked)): ?>
                        <button type="submit" id="saveBtn" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            <?php echo e($isEdit ? 'Update Entry' : 'Save Entry'); ?>

                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (! ($locked)): ?>
    <script>
        (function() {
            const form = document.getElementById('entryForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const account = document.getElementById('account');
            const isEdit = <?php echo e($isEdit ? 'true' : 'false'); ?>;
            const listUrl = "<?php echo e(route('admin.income-expenses.index')); ?>";

            // show only the account types that fit Income or Expense
            function filterAccounts() {
                const type = form.querySelector('[name="type"]:checked')?.value;
                const wanted = type === 'A' ? 'Income' : 'Expense';
                account.querySelectorAll('option[data-category]').forEach(o => {
                    const ok = o.dataset.category === wanted || o.dataset.category === 'Both';
                    o.hidden = !ok;
                    o.disabled = !ok;
                });
                if (account.selectedOptions[0]?.disabled) account.value = '';
            }
            form.querySelectorAll('[name="type"]').forEach(r => r.addEventListener('change', filterAccounts));
            filterAccounts();

            function clearErrors() {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            function showErrors(errors) {
                let first = null;
                Object.entries(errors).forEach(([field, messages]) => {
                    const input = form.querySelector('[name="' + field + '"]') || document.getElementById(field);
                    if (!input) return;
                    input.classList.add('is-invalid');
                    const box = input.closest('.mb-3').querySelector('.invalid-feedback');
                    if (box) {
                        box.textContent = messages[0];
                        box.classList.add('d-block');
                    }
                    first = first || input;
                });
                first?.focus();
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();
                form.querySelectorAll('.invalid-feedback.d-block').forEach(el => el.classList.remove('d-block'));
                saveBtn.disabled = true;
                spinner.classList.remove('d-none');

                fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 'Accept': 'application/json' }
                    })
                    .then(async r => ({ status: r.status, data: await r.json() }))
                    .then(({ status, data }) => {
                        if (status === 422 && data.errors) return showErrors(data.errors);
                        if (!data.success) return Swal.fire('Error!', data.message || 'Could not save. Please try again.', 'error');

                        Swal.fire({
                            icon: 'success',
                            title: isEdit ? 'Updated!' : 'Saved!',
                            text: data.message,
                            showCancelButton: !isEdit,
                            confirmButtonText: isEdit ? 'OK' : 'Add Another',
                            cancelButtonText: 'Go to List',
                        }).then(result => {
                            // "Add Another" keeps the same type, date, account and division
                            if (!isEdit && result.isConfirmed) location.href = data.again;
                            else location.href = listUrl;
                        });
                    })
                    .catch(() => Swal.fire('Error!', 'Could not save. Please try again.', 'error'))
                    .finally(() => {
                        saveBtn.disabled = false;
                        spinner.classList.add('d-none');
                    });
            });
        })();
    </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => $isEdit ? 'Entry Edit' : 'Entry Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/income-expenses/create.blade.php ENDPATH**/ ?>