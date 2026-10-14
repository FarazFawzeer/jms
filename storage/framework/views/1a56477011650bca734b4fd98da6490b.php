<?php
    use App\Models\Customer;
    $isEdit = $customer->exists;
    $title = trim((string) $customer->Contract_Person);
    $type = trim((string) $customer->Email);
    $religion = trim((string) $customer->Fax);
?>



<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Customers', 'subtitle' => $isEdit ? 'Edit' : 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0"><?php echo e($isEdit ? 'Edit Customer' : 'New Customer'); ?></h5>
            <span class="badge badge-soft-primary fs-13">Customer No: <?php echo e($isEdit ? $customer->code : $nextCode); ?></span>
        </div>

        <div class="card-body">

            <form id="customerForm" novalidate
                action="<?php echo e($isEdit ? route('admin.customers.update', $customer->code) : route('admin.customers.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="title" class="form-label">Title</label>
                        <select id="title" name="title" class="form-select">
                            <option value="">Select</option>
                            <?php $__currentLoopData = Customer::TITLES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($t); ?>" <?php if($title === $t): echo 'selected'; endif; ?>><?php echo e($t); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-9 mb-3">
                        <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" maxlength="200"
                            value="<?php echo e(trim((string) $customer->Cust_Name)); ?>" placeholder="Ex: Mohamed Rizwan" autocomplete="off" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" inputmode="numeric" maxlength="15"
                            value="<?php echo e($customer->Tel1 ? Customer::formatPhone($customer->Tel1) : ''); ?>" placeholder="Ex: 077 123 4567">
                        <div class="invalid-feedback"></div>
                        <div id="phoneMatch" class="form-text text-warning d-none"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                        <select id="type" name="type" class="form-select" required>
                            <?php $__currentLoopData = Customer::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if($type === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control" maxlength="100"
                            value="<?php echo e(trim((string) $customer->Add1)); ?>" placeholder="Ex: 12, Main Street">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label">City / Town</label>
                        <input type="text" id="city" name="city" class="form-control" maxlength="100"
                            value="<?php echo e(trim((string) $customer->Add3)); ?>" placeholder="Ex: Kandy">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone2" class="form-label">Other Phone</label>
                        <input type="tel" id="phone2" name="phone2" class="form-control" inputmode="numeric" maxlength="15"
                            value="<?php echo e($customer->Tel2 ? Customer::formatPhone($customer->Tel2) : ''); ?>" placeholder="Ex: 081 222 3344">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="religion" class="form-label">Religion</label>
                        <select id="religion" name="religion" class="form-select">
                            <option value="">Select</option>
                            <?php $__currentLoopData = Customer::RELIGIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if($religion === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('admin.customers.index')); ?>" class="btn btn-light">Cancel</a>
                    <button type="submit" id="saveBtn" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        <?php echo e($isEdit ? 'Update' : 'Save'); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('customerForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const isEdit = <?php echo e($isEdit ? 'true' : 'false'); ?>;
            const listUrl = "<?php echo e(route('admin.customers.index')); ?>";

            // 0771234567 -> 077 123 4567 while typing
            form.querySelectorAll('input[type=tel]').forEach(input => {
                input.addEventListener('input', () => {
                    let d = input.value.replace(/\D/g, '');
                    if (d.startsWith('94') && d.length > 10) d = '0' + d.slice(2);
                    d = d.slice(0, 10);
                    input.value = [d.slice(0, 3), d.slice(3, 6), d.slice(6)].filter(Boolean).join(' ');
                });
            });

            // warn if this mobile number is already saved for another customer
            const mobile = document.getElementById('mobile');
            const phoneMatch = document.getElementById('phoneMatch');
            mobile.addEventListener('input', () => {
                const digits = mobile.value.replace(/\D/g, '');
                phoneMatch.classList.add('d-none');
                if (digits.length !== 10) return;
                const p = new URLSearchParams({ phone: digits, except: "<?php echo e($isEdit ? $customer->code : ''); ?>" });
                fetch("<?php echo e(route('admin.customers.check-phone')); ?>?" + p, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.match) return;
                        phoneMatch.innerHTML = 'Already saved for <strong></strong> (No. ' + data.match.code + '). <a href="' + data.match.url + '">Open</a>';
                        phoneMatch.querySelector('strong').textContent = data.match.name;
                        phoneMatch.classList.remove('d-none');
                    })
                    .catch(() => {});
            });

            function clearErrors() {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            function showErrors(errors) {
                let first = null;
                Object.entries(errors).forEach(([field, messages]) => {
                    const input = form.querySelector('[name="' + field + '"]');
                    if (!input) return;
                    input.classList.add('is-invalid');
                    const box = input.parentElement.querySelector('.invalid-feedback');
                    if (box) box.textContent = messages[0];
                    first = first || input;
                });
                first?.focus();
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();
                saveBtn.disabled = true;
                spinner.classList.remove('d-none');

                fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 'Accept': 'application/json' }
                    })
                    .then(async r => ({ status: r.status, data: await r.json() }))
                    .then(({ status, data }) => {
                        if (status === 422) return showErrors(data.errors);
                        if (!data.success) throw new Error();

                        Swal.fire({
                            icon: 'success',
                            title: isEdit ? 'Updated!' : 'Saved!',
                            text: data.message,
                            showCancelButton: !isEdit,
                            confirmButtonText: isEdit ? 'OK' : 'Add Another',
                            cancelButtonText: 'Go to List',
                        }).then(result => {
                            if (!isEdit && result.isConfirmed) location.reload();
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => $isEdit ? 'Customer Edit' : 'Customer Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/customers/create.blade.php ENDPATH**/ ?>