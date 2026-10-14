{{-- Add these <li class="nav-item"> blocks right under the "Suppliers" item in your sidebar. --}}
{{-- Company and Divisions are shown only to admins of the Overall division. --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
        <span class="nav-icon">
            <iconify-icon icon="solar:tag-price-outline"></iconify-icon>
        </span>
        <span class="nav-text"> Products </span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
        <span class="nav-icon">
            <iconify-icon icon="solar:widget-4-outline"></iconify-icon>
        </span>
        <span class="nav-text"> Categories </span>
    </a>
</li>
@if (auth()->user()?->canManageSystem())
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.company.*') ? 'active' : '' }}" href="{{ route('admin.company.index') }}">
            <span class="nav-icon">
                <iconify-icon icon="solar:shop-2-outline"></iconify-icon>
            </span>
            <span class="nav-text"> Company </span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.divisions.*') ? 'active' : '' }}" href="{{ route('admin.divisions.index') }}">
            <span class="nav-icon">
                <iconify-icon icon="solar:buildings-2-outline"></iconify-icon>
            </span>
            <span class="nav-text"> Divisions </span>
        </a>
    </li>
@endif
