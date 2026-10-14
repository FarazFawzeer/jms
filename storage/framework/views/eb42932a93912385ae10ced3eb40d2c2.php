<?php use App\Models\Customer; ?>



<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Company', 'subtitle' => 'Details'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Company Details</h5>
                <p class="card-subtitle mb-0">Shared by all divisions. Printed on bills and tags.</p>
            </div>
            <a href="<?php echo e(route('admin.divisions.index')); ?>" class="btn btn-light">
                <iconify-icon icon="solar:buildings-2-outline" class="align-middle me-1"></iconify-icon> Divisions
            </a>
        </div>

        <div class="card-body">
            <form id="companyForm" novalidate action="<?php echo e(route('admin.company.update')); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" maxlength="150"
                            value="<?php echo e(trim((string) $company->comp_desc)); ?>" placeholder="Ex: Little Lanka Jewellers" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" maxlength="50"
                            value="<?php echo e(trim((string) $company->E_MailAddress)); ?>" placeholder="Ex: info@shop.lk">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="add1" class="form-label">Address Line 1</label>
                        <input type="text" id="add1" name="add1" class="form-control" maxlength="50"
                            value="<?php echo e(trim((string) $company->Add1)); ?>" placeholder="Ex: 45, Main Street">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="add2" class="form-label">Address Line 2</label>
                        <input type="text" id="add2" name="add2" class="form-control" maxlength="250"
                            value="<?php echo e(trim((string) $company->Add2)); ?>" placeholder="Ex: Colombo 11">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="add3" class="form-label">Address Line 3</label>
                        <input type="text" id="add3" name="add3" class="form-control" maxlength="250"
                            value="<?php echo e(trim((string) $company->Add3)); ?>" placeholder="Optional">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tel1" class="form-label">Phone</label>
                        <input type="tel" id="tel1" name="tel1" class="form-control" inputmode="numeric" maxlength="15"
                            value="<?php echo e(trim((string) $company->Tel1) ? Customer::formatPhone($company->Tel1) : ''); ?>" placeholder="Ex: 077 123 4567">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tel2" class="form-label">Other Phone</label>
                        <input type="tel" id="tel2" name="tel2" class="form-control" inputmode="numeric" maxlength="15"
                            value="<?php echo e(trim((string) $company->Tel2) ? Customer::formatPhone($company->Tel2) : ''); ?>" placeholder="Ex: 011 222 3344">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <h6 class="text-muted text-uppercase fs-12 mt-2 mb-3">Gold Rates (per gram)</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="buy_rate" class="form-label">Buying Rate</label>
                        <input type="number" id="buy_rate" name="buy_rate" class="form-control" step="0.01" min="0" inputmode="decimal"
                            value="<?php echo e($company->Pu_Rate); ?>" placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sell_rate" class="form-label">Selling Rate</label>
                        <input type="number" id="sell_rate" name="sell_rate" class="form-control" step="0.01" min="0" inputmode="decimal"
                            value="<?php echo e($company->Sel_Rate); ?>" placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <h6 class="text-muted text-uppercase fs-12 mt-2 mb-3">Printers</h6>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="barcode_printer" class="form-label">Barcode / Tag Printer</label>
                        <input type="text" id="barcode_printer" name="barcode_printer" class="form-control" maxlength="150"
                            value="<?php echo e(trim((string) $company->BarcodeP)); ?>">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="barcode_port" class="form-label">Port</label>
                        <input type="text" id="barcode_port" name="barcode_port" class="form-control" maxlength="50"
                            value="<?php echo e(trim((string) $company->BarcodePort)); ?>">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="ticket_printer" class="form-label">Bill Printer</label>
                        <input type="text" id="ticket_printer" name="ticket_printer" class="form-control" maxlength="150"
                            value="<?php echo e(trim((string) $company->TicketP)); ?>">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ticket_port" class="form-label">Port</label>
                        <input type="text" id="ticket_port" name="ticket_port" class="form-control" maxlength="50"
                            value="<?php echo e(trim((string) $company->TicketPort)); ?>">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="doc_printer" class="form-label">Document Printer</label>
                        <input type="text" id="doc_printer" name="doc_printer" class="form-control" maxlength="150"
                            value="<?php echo e(trim((string) $company->DocP)); ?>">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="doc_port" class="form-label">Port</label>
                        <input type="text" id="doc_port" name="doc_port" class="form-control" maxlength="50"
                            value="<?php echo e(trim((string) $company->DocPort)); ?>">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" id="saveBtn" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        Save Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('companyForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');

            // 0771234567 -> 077 123 4567 while typing
            form.querySelectorAll('input[type=tel]').forEach(input => {
                input.addEventListener('input', () => {
                    let d = input.value.replace(/\D/g, '');
                    if (d.startsWith('94') && d.length > 10) d = '0' + d.slice(2);
                    d = d.slice(0, 10);
                    input.value = [d.slice(0, 3), d.slice(3, 6), d.slice(6)].filter(Boolean).join(' ');
                });
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
                        Swal.fire({ icon: 'success', title: 'Saved!', text: data.message, timer: 1500, showConfirmButton: false });
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

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Company'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/company/index.blade.php ENDPATH**/ ?>