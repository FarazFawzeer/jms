@php
    use App\Support\Division;
    $isEdit = $product->exists;
    $locked = $isEdit && ! $product->isEditable();
    $category = $product->category_code;
    $supplier = trim((string) $product->TagNo);
@endphp

@extends('layouts.vertical', ['subtitle' => $isEdit ? 'Product Edit' : 'Product Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Products', 'subtitle' => $isEdit ? 'Edit' : 'Create'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">{{ $isEdit ? ($locked ? 'Item Details' : 'Edit Product') : 'New Product' }}</h5>
            <span class="badge badge-soft-primary fs-13">
                {{ $isEdit ? 'Barcode: ' . $product->barcode_no : 'Barcode is created on save' }}
            </span>
        </div>

        <div class="card-body">
            @if ($locked)
                <div class="alert alert-info">This item is {{ strtolower($product->sold_label[0]) }}, so it can no longer be changed.</div>
            @endif

            <form id="productForm" novalidate
                action="{{ $isEdit ? route('admin.products.update', $product->code) : route('admin.products.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <fieldset @disabled($locked)>
                {{-- Division --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="division" class="form-label">Division @if ($seesAll && ! $isEdit)<span class="text-danger">*</span>@endif</label>
                        @if ($seesAll && ! $isEdit)
                            <select id="division" name="division" class="form-select" required>
                                <option value="">Select Division</option>
                                @foreach ($divisions as $d)
                                    <option value="{{ $d->code }}" @selected($product->division_code === $d->code)>{{ $d->display_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" id="division" class="form-control" readonly
                                value="{{ $isEdit ? $product->division_name : Division::label() }}">
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="supplier" class="form-label">Supplier</label>
                        <select id="supplier" name="supplier" class="form-select">
                            <option value="">None</option>
                            @foreach ($suppliers as $s)
                                @continue(strlen($s->user_code) > 8)
                                <option value="{{ $s->user_code }}" @selected($supplier === $s->user_code)>{{ $s->display_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Category + Karat --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="category" name="category" class="form-select" @disabled($isEdit) required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->code }}" @selected($category === $c->code)>{{ $c->display_name }}</option>
                            @endforeach
                        </select>
                        @if ($isEdit)
                            <div class="form-text">The category is part of the barcode, so it cannot change.</div>
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unit" class="form-label">Karat / Metal <span class="text-danger">*</span></label>
                        <select id="unit" name="unit" class="form-select" required>
                            <option value="">Select Karat</option>
                            @foreach ($units as $code => $name)
                                <option value="{{ $code }}" @selected(trim((string) $product->unit_code) === (string) $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" id="description" name="description" class="form-control" maxlength="100"
                            value="{{ $product->display_name }}" placeholder="Same as category name" autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Weights --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label">Weight (g) <span class="text-danger">*</span></label>
                        <input type="number" id="weight" name="weight" class="form-control" step="0.001" min="0" inputmode="decimal"
                            value="{{ $isEdit ? $product->item_size : '' }}" placeholder="Ex: 4.250" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stone_weight" class="form-label">Stone Weight (g)</label>
                        <input type="number" id="stone_weight" name="stone_weight" class="form-control" step="0.001" min="0" inputmode="decimal"
                            value="{{ $isEdit && $product->Stone_weight ? $product->Stone_weight : '' }}" placeholder="0.000">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                </fieldset>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-light">{{ $locked ? 'Back' : 'Cancel' }}</a>
                    @unless ($locked)
                        <button type="submit" id="saveBtn" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            {{ $isEdit ? 'Update Product' : 'Save Product' }}
                        </button>
                    @endunless
                </div>
            </form>
        </div>
    </div>

    @unless ($locked)
    <script>
        (function() {
            const form = document.getElementById('productForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const isEdit = {{ $isEdit ? 'true' : 'false' }};
            const listUrl = "{{ route('admin.products.index') }}";

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
    @endunless
@endsection
