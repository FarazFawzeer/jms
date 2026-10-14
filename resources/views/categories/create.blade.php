@php $isEdit = $category->exists; @endphp

@extends('layouts.vertical', ['subtitle' => $isEdit ? 'Category Edit' : 'Category Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Categories', 'subtitle' => $isEdit ? 'Edit' : 'Create'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">{{ $isEdit ? 'Edit Category' : 'New Category' }}</h5>
            <span class="badge badge-soft-primary fs-13">Category No: {{ $isEdit ? $category->code : $nextCode }}</span>
        </div>

        <div class="card-body">
            <form id="categoryForm" novalidate
                action="{{ $isEdit ? route('admin.categories.update', $category->code) : route('admin.categories.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" maxlength="50"
                            value="{{ $category->display_name }}" placeholder="Ex: GENTS RING" autocomplete="off" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="short_code" class="form-label">Short Code <span class="text-danger">*</span></label>
                        <input type="text" id="short_code" name="short_code" class="form-control" maxlength="3"
                            value="{{ $category->short_code }}" placeholder="Ex: GR" autocomplete="off" required>
                        <div class="form-text">Up to 3 letters, printed on tags.</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" id="saveBtn" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        {{ $isEdit ? 'Update Category' : 'Save Category' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('categoryForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const isEdit = {{ $isEdit ? 'true' : 'false' }};
            const listUrl = "{{ route('admin.categories.index') }}";

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
@endsection
