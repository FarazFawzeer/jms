@extends('layouts.vertical', ['subtitle' => 'Admin View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Admin', 'subtitle' => 'View'])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Admin List</h5>
                <p class="card-subtitle mb-0">All admins in your system with details.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <iconify-icon icon="solar:add-circle-outline" class="align-middle me-1"></iconify-icon> Add Admin
            </a>
        </div>

        <div class="card-body">
            {{-- Search + filter: results update automatically --}}
            <form id="filterForm" action="{{ route('admin.users.index') }}" class="row g-2 justify-content-end mb-3" onsubmit="return false">
                <div class="col-12 col-md-4">
                    <input type="search" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Search name or email" autocomplete="off">
                </div>
                <div class="col-6 col-md-auto">
                    <select name="division" class="form-select">
                        <option value="">All Divisions</option>
                        @foreach ($divisions as $d)
                            <option value="{{ $d->code }}" @selected(request('division') === $d->code)>{{ $d->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <select name="type" class="form-select">
                        <option value="">All Roles</option>
                        @foreach ($types as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div id="userTable">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col" class="d-none d-md-table-cell">Division</th>
                                <th scope="col" class="d-none d-md-table-cell">Updated At</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $user->image_path ? asset($user->image_path) : asset('/images/users/avatar-6.jpg') }}"
                                                alt="{{ $user->name }}" class="avatar-sm rounded-circle">
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->type === 'Super Admin' ? 'badge-soft-danger' : ($user->type === 'Admin' ? 'badge-soft-primary' : 'badge-soft-success') }}">
                                            {{ $user->type }}
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $user->division_name }}</td>
                                    <td class="d-none d-md-table-cell">{{ $user->updated_at?->format('d M Y, h:i A') }}</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-link btn-sm p-1 text-primary"
                                            title="Edit" aria-label="Edit">
                                            <iconify-icon icon="solar:pen-2-outline" class="align-middle fs-20"></iconify-icon>
                                        </a>
                                        @if ($user->type !== 'Super Admin' && $user->id !== auth()->id())
                                            <button type="button" class="btn btn-link btn-sm p-1 text-danger delete-user"
                                                data-url="{{ route('admin.users.destroy', $user->id) }}" data-name="{{ $user->name }}"
                                                title="Delete" aria-label="Delete">
                                                <iconify-icon icon="solar:trash-bin-trash-outline" class="align-middle fs-20"></iconify-icon>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="mt-3">
                        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const tableBox = document.getElementById('userTable');
            let timer;

            // reload only the table, keep the page where it is
            function load(url) {
                tableBox.style.opacity = .5;
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const page = new DOMParser().parseFromString(html, 'text/html');
                        tableBox.innerHTML = page.getElementById('userTable').innerHTML;
                        history.replaceState(null, '', url);
                    })
                    .catch(() => Swal.fire('Error!', 'Could not load users.', 'error'))
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
            form.type.addEventListener('change', () => load(filterUrl()));
            form.division.addEventListener('change', () => load(filterUrl()));

            tableBox.addEventListener('click', function(e) {
                const pageLink = e.target.closest('.pagination a');
                if (pageLink) {
                    e.preventDefault();
                    load(pageLink.href);
                    return;
                }

                const button = e.target.closest('.delete-user');
                if (!button) return;

                Swal.fire({
                    title: 'Delete ' + button.dataset.name + '?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(button.dataset.url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                Swal.fire('Error!', data.message || 'Something went wrong!', 'error');
                                return;
                            }
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, timer: 1300, showConfirmButton: false });
                            load(location.href);
                        })
                        .catch(() => Swal.fire('Error!', 'Something went wrong!', 'error'));
                });
            });
        })();
    </script>
@endsection
