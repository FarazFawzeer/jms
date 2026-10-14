@php $isEdit = $user->exists; @endphp

@extends('layouts.vertical', ['subtitle' => $isEdit ? 'Admin Edit' : 'Admin Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Admin', 'subtitle' => $isEdit ? 'Edit' : 'Create'])


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $isEdit ? 'Edit User' : 'New User' }}</h5>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                <iconify-icon icon="solar:arrow-left-outline" class="align-middle me-1"></iconify-icon> Back
            </a>
        </div>

        <div class="card-body">
            <div id="message"></div> {{-- Success / Error messages --}}

            <form id="createUserForm" action="{{ $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                {{-- Name + Email in same row --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}"
                            placeholder="Ex: John Doe" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}"
                            placeholder="Ex: admin@gmail.com" required>
                    </div>
                </div>

                {{-- Password + Confirm Password in same row --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="{{ $isEdit ? 'Leave blank to keep the current password' : 'Password' }}" @required(! $isEdit)>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Re-enter Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="Re-enter Password" class="form-control" @required(! $isEdit)>
                    </div>
                </div>

                {{-- Type + Division in same row --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">User Type</label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="">Select Type</option>
                            @foreach ($types as $t)
                                <option value="{{ $t }}" @selected(old('type', $user->type) === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="division" class="form-label">Division</label>
                        <select id="division" name="division" class="form-select">
                            <option value="">Select Division</option>
                            @foreach ($divisions as $d)
                                <option value="{{ $d->code }}" @selected(trim((string) old('division', $user->stock_loc_sys_code)) === $d->code)>
                                    {{ $d->display_name }}{{ $d->Is_Main ? ' (Overall, sees all divisions)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">The user sees and enters records of this division only. A Super Admin with no division sees all.</div>
                    </div>
                </div>

                {{-- Profile Image --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="image_path" class="form-label">Profile Image</label>
                        <input type="file" id="image_path" name="image_path" class="form-control" accept="image/*">
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update User' : 'Create User' }}</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let form = this;
            let formData = new FormData(form);
            const isEdit = {{ $isEdit ? 'true' : 'false' }};

            fetch(form.action, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Accept": "application/json"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    let messageBox = document.getElementById('message');
                    if (data.success) {
                        messageBox.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        if (!isEdit) form.reset();

                        setTimeout(() => {
                            messageBox.innerHTML = "";
                        }, 3000);
                    } else {
                        // show validation errors
                        let errors = Object.values(data.errors).flat().join('<br>');
                        messageBox.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                    }
                })
                .catch(error => {
                    document.getElementById('message').innerHTML =
                        `<div class="alert alert-danger">Error: ${error}</div>`;
                });
        });
    </script>
@endsection
