{{-- Replace the existing "Admin" <li class="nav-item"> block in your sidebar with this one. --}}
{{-- Shown only to admins of the Overall division (they manage users and their divisions). --}}
@if (auth()->user()?->canManageSystem())
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
            <span class="nav-icon">
                <iconify-icon icon="solar:user-circle-outline"></iconify-icon>
            </span>
            <span class="nav-text"> Admin </span>
        </a>
    </li>
@endif
