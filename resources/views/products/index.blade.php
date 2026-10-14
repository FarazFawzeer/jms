@php use App\Support\Division; @endphp

@extends('layouts.vertical', ['subtitle' => 'Products'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Products', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Product List</h5>
                <p class="card-subtitle mb-0">Every piece with its barcode. Showing: {{ Division::label() }}.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> New Product
            </a>
        </div>

        <div class="card-body">
            {{-- Search + filters: results update automatically --}}
            <form id="filterForm" action="{{ route('admin.products.index') }}" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-3">
                    <input type="search" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Scan or search barcode / name" autocomplete="off">
                </div>
                <div class="col-6 col-md-auto">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->code }}" @selected(request('category') === $c->code)>{{ $c->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <select name="unit" class="form-select">
                        <option value="">All Karats</option>
                        @foreach ($units as $code => $name)
                            <option value="{{ $code }}" @selected(request('unit') === (string) $code)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($seesAll)
                    <div class="col-6 col-md-auto">
                        <select name="division" class="form-select">
                            <option value="">All Divisions</option>
                            @foreach ($divisions as $d)
                                <option value="{{ $d->code }}" @selected(request('division') === $d->code)>{{ $d->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-6 col-md-auto">
                    <select name="status" class="form-select">
                        <option value="stock" @selected($status === 'stock')>In Stock</option>
                        <option value="sold" @selected($status === 'sold')>Sold</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        <option value="all" @selected($status === 'all')>All</option>
                    </select>
                </div>
            </form>

            <div id="productTable">
            <p class="text-muted mb-2">
                {{ number_format($totals->pieces ?? 0) }} items &middot; {{ number_format($totals->weight ?? 0, 3) }} g
            </p>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Barcode</th>
                            <th scope="col">Item</th>
                            <th scope="col" class="d-none d-md-table-cell">Karat</th>
                            <th scope="col" class="text-end">Weight (g)</th>
                            <th scope="col" class="d-none d-lg-table-cell text-end">Stone (g)</th>
                            @if ($seesAll)
                                <th scope="col" class="d-none d-md-table-cell">Division</th>
                            @endif
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $p)
                            @php [$soldText, $soldBadge] = $p->sold_label; @endphp
                            <tr>
                                <td class="text-nowrap">{{ $p->barcode_no }}</td>
                                <td>
                                    <h6 class="mb-0">{{ $p->display_name }}</h6>
                                    <small class="text-muted">{{ $categories->firstWhere('code', $p->category_code)?->display_name ?? $p->category_code }}</small>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $units[trim((string) $p->unit_code)] ?? '-' }}</td>
                                <td class="text-end">{{ number_format($p->item_size, 3) }}</td>
                                <td class="d-none d-lg-table-cell text-end">{{ $p->Stone_weight ? number_format($p->Stone_weight, 3) : '-' }}</td>
                                @if ($seesAll)
                                    <td class="d-none d-md-table-cell">{{ $p->division_name }}</td>
                                @endif
                                <td>
                                    @if (! $p->isActive())
                                        <span class="badge badge-soft-danger">Inactive</span>
                                    @else
                                        <span class="badge {{ $soldBadge }}">{{ $soldText }}</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.products.edit', $p->code) }}" class="btn btn-link btn-sm p-1 text-primary"
                                        title="{{ $p->isEditable() ? 'Edit' : 'View' }}" aria-label="{{ $p->isEditable() ? 'Edit' : 'View' }}">
                                        <iconify-icon icon="{{ $p->isEditable() ? 'solar:pen-2-outline' : 'solar:eye-outline' }}" class="align-middle fs-20"></iconify-icon>
                                    </a>
                                    @if ($p->inStock())
                                        <button type="button" class="btn btn-link btn-sm p-1 {{ $p->isActive() ? 'text-danger' : 'text-success' }} toggle-status"
                                            data-url="{{ route('admin.products.status', $p->code) }}" data-active="{{ $p->isActive() ? 1 : 0 }}"
                                            data-name="{{ $p->display_name }} ({{ $p->barcode_no }})" title="{{ $p->isActive() ? 'Deactivate' : 'Activate' }}"
                                            aria-label="{{ $p->isActive() ? 'Deactivate' : 'Activate' }}">
                                            <iconify-icon icon="{{ $p->isActive() ? 'solar:forbidden-circle-outline' : 'solar:check-circle-outline' }}" class="align-middle fs-20"></iconify-icon>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $seesAll ? 8 : 7 }}" class="text-center text-muted py-4">
                                    {{ request('q') ? 'No items found for "' . request('q') . '".' : 'No items found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="mt-3">
                    {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('productTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('productTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load items.', 'error'))
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
                    title: active ? 'Deactivate item?' : 'Activate item?',
                    text: active ? button.dataset.name + ' will be removed from the stock list.' : button.dataset.name + ' will be back in stock.',
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
