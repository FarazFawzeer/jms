@php use App\Support\Division; @endphp

@extends('layouts.vertical', ['subtitle' => 'Categories'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Categories', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Category List</h5>
                <p class="card-subtitle mb-0">Item categories are shared by all divisions. Stock shown for: {{ Division::label() }}.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Category
            </a>
        </div>

        <div class="card-body">
            {{-- Search + filter: results update automatically --}}
            <form id="filterForm" action="{{ route('admin.categories.index') }}" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-4">
                    <input type="search" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search name, no. or short code" autocomplete="off">
                </div>
                <div class="col-12 col-md-auto">
                    <select name="status" class="form-select">
                        <option value="active" @selected($status === 'active')>Active ({{ $counts['active'] }})</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive ({{ $counts['inactive'] }})</option>
                        <option value="all" @selected($status === 'all')>All ({{ $counts['all'] }})</option>
                    </select>
                </div>
            </form>

            <div id="categoryTable">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Category Name</th>
                            <th scope="col">Short Code</th>
                            <th scope="col" class="text-end">In Stock</th>
                            <th scope="col" class="d-none d-md-table-cell text-end">Weight (g)</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $c)
                            @php $s = $stock[$c->code] ?? null; @endphp
                            <tr>
                                <td class="text-muted">{{ $c->code }}</td>
                                <td>
                                    <a href="{{ route('admin.products.index', ['category' => $c->code]) }}" class="text-reset">
                                        <h6 class="mb-0">{{ $c->display_name }}</h6>
                                    </a>
                                </td>
                                <td>{{ $c->short_code ?: '-' }}</td>
                                <td class="text-end">{{ number_format($s->pieces ?? 0) }}</td>
                                <td class="d-none d-md-table-cell text-end">{{ number_format($s->weight ?? 0, 3) }}</td>
                                <td>
                                    @if ($c->isActive())
                                        <span class="badge badge-soft-success">Active</span>
                                    @else
                                        <span class="badge badge-soft-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.categories.edit', $c->code) }}" class="btn btn-link btn-sm p-1 text-primary"
                                        title="Edit" aria-label="Edit">
                                        <iconify-icon icon="solar:pen-2-outline" class="align-middle fs-20"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn btn-link btn-sm p-1 {{ $c->isActive() ? 'text-danger' : 'text-success' }} toggle-status"
                                        data-url="{{ route('admin.categories.status', $c->code) }}" data-active="{{ $c->isActive() ? 1 : 0 }}"
                                        data-name="{{ $c->display_name }}" title="{{ $c->isActive() ? 'Deactivate' : 'Activate' }}"
                                        aria-label="{{ $c->isActive() ? 'Deactivate' : 'Activate' }}">
                                        <iconify-icon icon="{{ $c->isActive() ? 'solar:forbidden-circle-outline' : 'solar:check-circle-outline' }}" class="align-middle fs-20"></iconify-icon>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    {{ request('q') ? 'No categories found for "' . request('q') . '".' : 'No categories found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="mt-3">
                    {{ $categories->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('categoryTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('categoryTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load categories.', 'error'))
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
                    title: active ? 'Deactivate category?' : 'Activate category?',
                    text: active ? button.dataset.name + ' will not be offered for new items. Existing items keep it.' : button.dataset.name + ' will be active again.',
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
