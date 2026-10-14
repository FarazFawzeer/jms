{{-- Add this <li class="nav-item"> right under the "Customers" item in your sidebar. --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}" href="{{ route('admin.suppliers.index') }}">
        <span class="nav-icon">
            <iconify-icon icon="solar:delivery-outline"></iconify-icon>
        </span>
        <span class="nav-text"> Suppliers </span>
    </a>
</li>
