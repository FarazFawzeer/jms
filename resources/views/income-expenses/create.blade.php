@php
    use App\Models\AccountEntry;
    use App\Support\Division;
    $isEdit = $entry->exists;
    $locked = $isEdit && ! $entry->isActive();
@endphp

@extends('layouts.vertical', ['subtitle' => $isEdit ? 'Entry Edit' : 'Entry Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Income & Expenses', 'subtitle' => $isEdit ? 'Edit' : 'Create'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">{{ $isEdit ? ($locked ? 'Entry Details' : 'Edit Entry') : 'New Income / Expense' }}</h5>
            <span class="badge badge-soft-primary fs-13">
                {{ $isEdit ? 'Entry No: ' . $entry->entry_no : 'Entry no. is given on save' }}
            </span>
        </div>

        <div class="card-body">
            @if ($locked)
                <div class="alert alert-info">This entry is cancelled. Restore it from the list to change it.</div>
            @endif

            <form id="entryForm" novalidate
                action="{{ $isEdit ? route('admin.income-expenses.update', $entry->entry_no) : route('admin.income-expenses.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <fieldset @disabled($locked)>
                {{-- Income or expense --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Entry Type <span class="text-danger">*</span></label>
                        @foreach (AccountEntry::DIRECTIONS as $key => [$label])
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type{{ $key }}" value="{{ $key }}"
                                    @checked($entry->direction === $key)>
                                <label class="form-check-label" for="type{{ $key }}">{{ $label }} ({{ $key === AccountEntry::INCOME ? 'money in' : 'money out' }})</label>
                            </div>
                        @endforeach
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="division" class="form-label">Division @if ($seesAll && ! $isEdit)<span class="text-danger">*</span>@endif</label>
                        @if ($seesAll && ! $isEdit)
                            <select id="division" name="division" class="form-select" required>
                                <option value="">Select Division</option>
                                @foreach ($divisions as $d)
                                    <option value="{{ $d->code }}" @selected($entry->division_code === $d->code)>{{ $d->display_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" id="division" class="form-control" readonly
                                value="{{ $isEdit ? $entry->division_name : Division::label() }}">
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Date + account --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" id="date" name="date" class="form-control" max="{{ now()->format('Y-m-d') }}"
                            value="{{ $entry->Ent_date?->format('Y-m-d') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="account" class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select id="account" name="account" class="form-select" required>
                            <option value="">Select Account</option>
                            @foreach ($accounts as $a)
                                <option value="{{ $a->code }}" data-category="{{ $a->category }}" @selected($entry->account_code === $a->code)>{{ $a->display_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Amount + mode --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" inputmode="decimal"
                            value="{{ $isEdit ? $entry->amount : '' }}" placeholder="Ex: 5000.00" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pay_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select id="pay_mode" name="pay_mode" class="form-select" required>
                            @foreach (AccountEntry::PAY_MODES as $key => $label)
                                <option value="{{ $key }}" @selected($entry->pay_mode === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                {{-- Party + description --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="party" class="form-label">Paid To / Received From <span class="text-danger">*</span></label>
                        <select id="party" name="party" class="form-select" required>
                            @foreach ($parties as $p)
                                <option value="{{ trim($p->Cust_Sys_Code) }}" @selected($entry->party_code === trim($p->Cust_Sys_Code))>{{ trim($p->Cust_Name) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Leave as CASH when it is not for a customer.</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" id="description" name="description" class="form-control" maxlength="50"
                            value="{{ $entry->description_text }}" placeholder="Ex: ELECTRICITY BILL - SEPTEMBER" autocomplete="off" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                </fieldset>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.income-expenses.index') }}" class="btn btn-light">{{ $locked ? 'Back' : 'Cancel' }}</a>
                    @unless ($locked)
                        <button type="submit" id="saveBtn" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            {{ $isEdit ? 'Update Entry' : 'Save Entry' }}
                        </button>
                    @endunless
                </div>
            </form>
        </div>
    </div>

    @unless ($locked)
    <script>
        (function() {
            const form = document.getElementById('entryForm');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = saveBtn.querySelector('.spinner-border');
            const account = document.getElementById('account');
            const isEdit = {{ $isEdit ? 'true' : 'false' }};
            const listUrl = "{{ route('admin.income-expenses.index') }}";

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
    @endunless
@endsection
