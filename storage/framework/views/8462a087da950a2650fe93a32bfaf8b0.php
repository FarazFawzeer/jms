<?php
    use App\Support\Division;
    $isEdit = $product->exists;
    $locked = $isEdit && ! $product->isEditable();
    $category = $product->category_code;
    $supplier = trim((string) $product->TagNo);
?>



<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Products', 'subtitle' => $isEdit ? 'Edit' : 'Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0"><?php echo e($isEdit ? ($locked ? 'Item Details' : 'Edit Product') : 'New Product'); ?></h5>
            <span class="badge badge-soft-primary fs-13">
                <?php echo e($isEdit ? 'Barcode: ' . $product->barcode_no : 'Barcode is created on save'); ?>

            </span>
        </div>

        <div class="card-body">
            <?php if($locked): ?>
                <div class="alert alert-info">This item is <?php echo e(strtolower($product->sold_label[0])); ?>, so it can no longer be changed.</div>
            <?php endif; ?>

            <form id="productForm" novalidate
                action="<?php echo e($isEdit ? route('admin.products.update', $product->code) : route('admin.products.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                <fieldset <?php if($locked): echo 'disabled'; endif; ?>>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="division" class="form-label">Division <?php if($seesAll && ! $isEdit): ?><span class="text-danger">*</span><?php endif; ?></label>
                        <?php if($seesAll && ! $isEdit): ?>
                            <select id="division" name="division" class="form-select" required>
                                <option value="">Select Division</option>
                                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($d->code); ?>" <?php if($product->division_code === $d->code): echo 'selected'; endif; ?>><?php echo e($d->display_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php else: ?>
                            <input type="text" id="division" class="form-control" readonly
                                value="<?php echo e($isEdit ? $product->division_name : Division::label()); ?>">
                        <?php endif; ?>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="supplier" class="form-label">Supplier</label>
                        <select id="supplier" name="supplier" class="form-select">
                            <option value="">None</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(strlen($s->user_code) > 8) continue; ?>
                                <option value="<?php echo e($s->user_code); ?>" <?php if($supplier === $s->user_code): echo 'selected'; endif; ?>><?php echo e($s->display_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="category" name="category" class="form-select" <?php if($isEdit): echo 'disabled'; endif; ?> required>
                            <option value="">Select Category</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c->code); ?>" <?php if($category === $c->code): echo 'selected'; endif; ?>><?php echo e($c->display_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if($isEdit): ?>
                            <div class="form-text">The category is part of the barcode, so it cannot change.</div>
                        <?php endif; ?>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unit" class="form-label">Karat / Metal <span class="text-danger">*</span></label>
                        <select id="unit" name="unit" class="form-select" required>
                            <option value="">Select Karat</option>
                            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php if(trim((string) $product->unit_code) === (string) $code): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" id="description" name="description" class="form-control" maxlength="100"
                            value="<?php echo e($product->display_name); ?>" placeholder="Same as category name" autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label">Weight (g) <span class="text-danger">*</span></label>
                        <input type="number" id="weight" name="weight" class="form-control" step="0.001" min="0" inputmode="decimal"
                            value="<?php echo e($isEdit ? $product->item_size : ''); ?>" placeholder="Ex: 4.250" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stone_weight" class="form-label">Stone Weight (g)</label>
                        <input type="number" id="stone_weight" name="stone_weight" class="form-control" step="0.001" min="0" inputmode="decimal"
                            value="<?php echo e($isEdit && $product->Stone_weight ? $product->Stone_weight : ''); ?>" placeholder="0.000">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                </fieldset>

                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-light"><?php echo e($locked ? 'Back' : 'Cancel'); ?></a>
                    <?php if (! ($locked)): ?>
                        <button type="submit" id="saveBtn" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            <?php echo e($isEdit ? 'Update Product' : 'Save Product'); ?>

                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (! ($locked)): ?>
    <script>
        (function() {
            const form = document.getElementById('productForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const isEdit = <?php echo e($isEdit ? 'true' : 'false'); ?>;
            const listUrl = "<?php echo e(route('admin.products.index')); ?>";

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
                            // "Add Another" keeps the same category, karat, division and supplier
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

<?php echo $__env->make('layouts.vertical', ['subtitle' => $isEdit ? 'Product Edit' : 'Product Create'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Personal Projects\Infotech\jms\resources\views/products/create.blade.php ENDPATH**/ ?>