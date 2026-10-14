@php use App\Models\AccountType; @endphp

@extends('layouts.vertical', ['subtitle' => 'Account Types'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Account Types', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Account Types</h5>
                <p class="card-subtitle mb-0">Income and expense heads, shared by all divisions.</p>
            </div>
            <a href="{{ route('admin.account-types.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Account Type
            </a>
        </div>

        <div class="card-body">
            {{-- Search + filters: results update automatically --}}
            <form id="filterForm" action="{{ route('admin.account-types.index') }}" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-4">
                    <input type="search" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search account name or no." autocomplete="off">
                </div>
                <div class="col-6 col-md-auto">
                    <select name="category" class="form-select">
                        <option value="">All Types</option>
                        @foreach (AccountType::CATEGORIES as $key => [$label])
                            <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <select name="status" class="form-select">
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        <option value="all" @selected($status === 'all')>All</option>
                    </select>
                </div>
            </form>

            <div id="typeTable">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Account Name</th>
                            <th scope="col">Type</th>
                            <th scope="col" class="d-none d-md-table-cell text-end">Entries</th>
                            <th scope="col" class="d-none d-lg-table-cell text-end">Income</th>
                            <th scope="col" class="d-none d-lg-table-cell text-end">Expense</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($types as $t)
                            @php
                                [$catText, $catBadge] = $t->category_label;
                                $used = $usage[$t->code] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $t->code }}</td>
                                <td><h6 class="mb-0">{{ $t->display_name }}</h6></td>
                                <td><span class="badge {{ $catBadge }}">{{ $catText }}</span></td>
                                <td class="d-none d-md-table-cell text-end">{{ number_format($used->entries ?? 0) }}</td>
                                <td class="d-none d-lg-table-cell text-end">{{ $used && $used->income ? number_format($used->income, 2) : '-' }}</td>
                                <td class="d-none d-lg-table-cell text-end">{{ $used && $used->expense ? number_format($used->expense, 2) : '-' }}</td>
                                <td>
                                    @if ($t->isActive())
                                        <span class="badge badge-soft-success">Active</span>
                                    @else
                                        <span class="badge badge-soft-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.account-types.edit', $t->code) }}" class="btn btn-link btn-sm p-1 text-primary" title="Edit" aria-label="Edit">
                                        <iconify-icon icon="solar:pen-2-outline" class="align-middle fs-20"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn btn-link btn-sm p-1 {{ $t->isActive() ? 'text-danger' : 'text-success' }} toggle-status"
                                        data-url="{{ route('admin.account-types.status', $t->code) }}" data-active="{{ $t->isActive() ? 1 : 0 }}"
                                        data-name="{{ $t->display_name }}" title="{{ $t->isActive() ? 'Deactivate' : 'Activate' }}"
                                        aria-label="{{ $t->isActive() ? 'Deactivate' : 'Activate' }}">
                                        <iconify-icon icon="{{ $t->isActive() ? 'solar:forbidden-circle-outline' : 'solar:check-circle-outline' }}" class="align-middle fs-20"></iconify-icon>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    {{ request('q') ? 'No account types found for "' . request('q') . '".' : 'No account types found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($types->hasPages())
                <div class="mt-3">
                    {{ $types->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('typeTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('typeTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load account types.', 'error'))
                    .finally(() => tableBox.style.opacity = 1);
            }

            function filterUrl() {
                return form.action + '?' + new URLSearchParams(new FormData(form));
            }

            form.q.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => load(filterUrl()), 400);
            });
            form.querySelectorAll('select').forEach(s => s.addEventListener('change', () => load(filterUrl())));

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
                    title: active ? 'Deactivate account type?' : 'Activate account type?',
                    text: active ? button.dataset.name + ' will no longer be offered for new entries.' : button.dataset.name + ' can be used again.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: active ? '#d33' : '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: active ? 'Yes, deactivate' : 'Yes, activate'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(button.dataset.url, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
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
@endsection
