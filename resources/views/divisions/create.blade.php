@php $isEdit = $division->exists; @endphp

@extends('layouts.vertical', ['subtitle' => $isEdit ? 'Division Edit' : 'Division Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Divisions', 'subtitle' => $isEdit ? 'Edit' : 'Create'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">{{ $isEdit ? 'Edit Division' : 'New Division' }}</h5>
            <span class="badge badge-soft-primary fs-13">Division No: {{ $isEdit ? $division->user_code : str_pad($nextCode, 3, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="card-body">
            <form id="divisionForm" novalidate
                action="{{ $isEdit ? route('admin.divisions.update', $division->code) : route('admin.divisions.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                {{-- Name + No. --}}
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">Division Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" maxlength="50"
                            value="{{ $division->display_name }}" placeholder="Ex: Colombo Branch" autocomplete="off" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="user_code" class="form-label">Division No.</label>
                        <input type="text" id="user_code" name="user_code" class="form-control" maxlength="15"
                            value="{{ $isEdit ? $division->user_code : '' }}" placeholder="{{ $isEdit ? '' : str_pad($nextCode, 3, '0', STR_PAD_LEFT) }}" autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Address --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="add1" class="form-label">Address Line 1</label>
                        <input type="text" id="add1" name="add1" class="form-control" maxlength="50"
                            value="{{ trim((string) $division->add1) }}" placeholder="Ex: 45, Main Street">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="add2" class="form-label">Address Line 2</label>
                        <input type="text" id="add2" name="add2" class="form-control" maxlength="50"
                            value="{{ trim((string) $division->add2) }}" placeholder="Optional">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="add3" class="form-label">City / Town</label>
                        <input type="text" id="add3" name="add3" class="form-control" maxlength="50"
                            value="{{ trim((string) $division->add3) }}" placeholder="Ex: Colombo 11">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Overall --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_main" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_main" name="is_main" value="1" @checked($division->Is_Main)>
                        <label class="form-check-label" for="is_main">Overall division</label>
                    </div>
                    <div class="form-text">Users of the Overall division see the stock, sales and reports of every division. Stock is never kept in it.</div>
                    <div class="invalid-feedback d-block" id="is_mainError"></div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.divisions.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" id="saveBtn" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        {{ $isEdit ? 'Update Division' : 'Save Division' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('divisionForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const isEdit = {{ $isEdit ? 'true' : 'false' }};
            const listUrl = "{{ route('admin.divisions.index') }}";

            function clearErrors() {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            function showErrors(errors) {
                let first = null;
                Object.entries(errors).forEach(([field, messages]) => {
                    if (field === 'is_main') {
                        document.getElementById('is_mainError').textContent = messages[0];
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
