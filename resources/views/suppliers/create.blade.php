@php
    use App\Models\Customer;
    $isEdit = $supplier->exists;
@endphp

@extends('layouts.vertical', ['subtitle' => $isEdit ? 'Supplier Edit' : 'Supplier Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Suppliers', 'subtitle' => $isEdit ? 'Edit' : 'Create'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">{{ $isEdit ? 'Edit Supplier' : 'New Supplier' }}</h5>
            <span class="badge badge-soft-primary fs-13">Supplier No: {{ $isEdit ? $supplier->code : $nextCode }}</span>
        </div>

        <div class="card-body">

            <form id="supplierForm" novalidate
                action="{{ $isEdit ? route('admin.suppliers.update', $supplier->code) : route('admin.suppliers.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                {{-- Name + Code --}}
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" maxlength="50"
                            value="{{ trim((string) $supplier->Suppliers_Name) }}" placeholder="Ex: Lanka Gold Traders" autocomplete="off" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="user_code" class="form-label">Supplier Code</label>
                        <input type="text" id="user_code" name="user_code" class="form-control" maxlength="15"
                            value="{{ $isEdit ? $supplier->user_code : '' }}" placeholder="Same as Supplier No." autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Contact person + Mobile --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact" class="form-label">Contact Person</label>
                        <input type="text" id="contact" name="contact" class="form-control" maxlength="50"
                            value="{{ trim((string) $supplier->Contract_Person) }}" placeholder="Ex: Mohamed Rizwan">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" inputmode="numeric" maxlength="15"
                            value="{{ $supplier->Tel1 ? Customer::formatPhone($supplier->Tel1) : '' }}" placeholder="Ex: 077 123 4567">
                        <div class="invalid-feedback"></div>
                        <div id="phoneMatch" class="form-text text-warning d-none"></div>
                    </div>
                </div>

                {{-- Other phone + Email --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone2" class="form-label">Other Phone</label>
                        <input type="tel" id="phone2" name="phone2" class="form-control" inputmode="numeric" maxlength="15"
                            value="{{ $supplier->Tel2 ? Customer::formatPhone($supplier->Tel2) : '' }}" placeholder="Ex: 011 222 3344">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" maxlength="50"
                            value="{{ trim((string) $supplier->Email) }}" placeholder="Ex: sales@lankagold.lk">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Address + City --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control" maxlength="50"
                            value="{{ trim((string) $supplier->Add1) }}" placeholder="Ex: 45, Sea Street">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label">City / Town</label>
                        <input type="text" id="city" name="city" class="form-control" maxlength="50"
                            value="{{ trim((string) $supplier->Add3) }}" placeholder="Ex: Colombo 11">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- VAT no --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vat_no" class="form-label">VAT No.</label>
                        <input type="text" id="vat_no" name="vat_no" class="form-control" maxlength="15"
                            value="{{ trim((string) $supplier->Vat_No) }}" placeholder="Optional">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Categories this supplier provides --}}
                <div class="mb-3">
                    <label class="form-label mb-1">Categories Supplied</label>
                    <p class="text-muted fs-12 mb-2">Tick the item categories this supplier provides.</p>
                    <div class="row" id="categories">
                        @foreach ($categoryOptions as $cat)
                            <div class="col-6 col-md-4 col-lg-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ trim($cat->code) }}"
                                        id="cat{{ trim($cat->code) }}" @checked(in_array(trim($cat->code), $selected))>
                                    <label class="form-check-label" for="cat{{ trim($cat->code) }}">{{ trim($cat->name) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="invalid-feedback d-block" id="categoriesError"></div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" id="saveBtn" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        {{ $isEdit ? 'Update Supplier' : 'Save Supplier' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('supplierForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const isEdit = {{ $isEdit ? 'true' : 'false' }};
            const listUrl = "{{ route('admin.suppliers.index') }}";

            // 0771234567 -> 077 123 4567 while typing
            form.querySelectorAll('input[type=tel]').forEach(input => {
                input.addEventListener('input', () => {
                    let d = input.value.replace(/\D/g, '');
                    if (d.startsWith('94') && d.length > 10) d = '0' + d.slice(2);
                    d = d.slice(0, 10);
                    input.value = [d.slice(0, 3), d.slice(3, 6), d.slice(6)].filter(Boolean).join(' ');
                });
            });

            // warn if this mobile number is already saved for another supplier
            const mobile = document.getElementById('mobile');
            const phoneMatch = document.getElementById('phoneMatch');
            mobile.addEventListener('input', () => {
                const digits = mobile.value.replace(/\D/g, '');
                phoneMatch.classList.add('d-none');
                if (digits.length !== 10) return;
                const p = new URLSearchParams({ phone: digits, except: "{{ $isEdit ? $supplier->code : '' }}" });
                fetch("{{ route('admin.suppliers.check-phone') }}?" + p, { headers: { 'Accept': 'application/json' } })
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
                    if (field.startsWith('categories')) {
                        document.getElementById('categoriesError').textContent = messages[0];
                        return;
                    }
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
@endsection
