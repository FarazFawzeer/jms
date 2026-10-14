@extends('layouts.vertical', ['subtitle' => 'Divisions'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Divisions', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Division List</h5>
                <p class="card-subtitle mb-0">Users of a division see only that division's stock, sales and reports. Overall users see all.</p>
            </div>
            <a href="{{ route('admin.divisions.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Division
            </a>
        </div>

        <div class="card-body">
            {{-- Search + filter: results update automatically --}}
            <form id="filterForm" action="{{ route('admin.divisions.index') }}" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-4">
                    <input type="search" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search name, no. or address" autocomplete="off">
                </div>
                <div class="col-12 col-md-auto">
                    <select name="status" class="form-select">
                        <option value="active" @selected($status === 'active')>Active ({{ $counts['active'] }})</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive ({{ $counts['inactive'] }})</option>
                        <option value="all" @selected($status === 'all')>All ({{ $counts['all'] }})</option>
                    </select>
                </div>
            </form>

            <div id="divisionTable">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Division Name</th>
                            <th scope="col">Type</th>
                            <th scope="col" class="d-none d-md-table-cell text-end">Items in Stock</th>
                            <th scope="col" class="d-none d-md-table-cell text-end">Users</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($divisions as $d)
                            <tr>
                                <td class="text-muted">{{ $d->user_code }}</td>
                                <td>
                                    <h6 class="mb-0">{{ $d->display_name }}</h6>
                                    @if ($d->address)
                                        <small class="text-muted">{{ $d->address }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($d->Is_Main)
                                        <span class="badge badge-soft-primary">Overall</span>
                                    @else
                                        <span class="badge badge-soft-info">Division</span>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell text-end">{{ $d->Is_Main ? '-' : number_format($stock[$d->code] ?? 0) }}</td>
                                <td class="d-none d-md-table-cell text-end">{{ number_format($users[$d->code] ?? 0) }}</td>
                                <td>
                                    @if ($d->isActive())
                                        <span class="badge badge-soft-success">Active</span>
                                    @else
                                        <span class="badge badge-soft-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.divisions.edit', $d->code) }}" class="btn btn-link btn-sm p-1 text-primary"
                                        title="Edit" aria-label="Edit">
                                        <iconify-icon icon="solar:pen-2-outline" class="align-middle fs-20"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn btn-link btn-sm p-1 {{ $d->isActive() ? 'text-danger' : 'text-success' }} toggle-status"
                                        data-url="{{ route('admin.divisions.status', $d->code) }}" data-active="{{ $d->isActive() ? 1 : 0 }}"
                                        data-name="{{ $d->display_name }}" title="{{ $d->isActive() ? 'Deactivate' : 'Activate' }}"
                                        aria-label="{{ $d->isActive() ? 'Deactivate' : 'Activate' }}">
                                        <iconify-icon icon="{{ $d->isActive() ? 'solar:forbidden-circle-outline' : 'solar:check-circle-outline' }}" class="align-middle fs-20"></iconify-icon>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    {{ request('q') ? 'No divisions found for "' . request('q') . '".' : 'No divisions found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($divisions->hasPages())
                <div class="mt-3">
                    {{ $divisions->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('divisionTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('divisionTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load divisions.', 'error'))
                    .finally(() => tableBox.style.opacity = 1);
            }

            function filterUrl() {
                return form.action + '?' + new URLSearchParams(new FormData(form));
            }

            // filter automatically while typing / choosing
            form.q.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => load(filterUrl()), 400);
            });
            form.status.addEventListener('change', () => load(filterUrl()));

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
                    title: active ? 'Deactivate division?' : 'Activate division?',
                    text: active ? button.dataset.name + ' will be hidden from lists. Its stock, sales and reports stay as they are.' : button.dataset.name + ' will be active again.',
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
                            if (!data.success) throw new Error();
                            Swal.fire({ icon: 'success', title: 'Done', text: data.message, timer: 1300, showConfirmButton: false })
                                .then(() => location.reload());
                        })
                        .catch(() => Swal.fire('Error!', 'Something went wrong!', 'error'));
                });
            });
        })();
    </script>
@endsection
