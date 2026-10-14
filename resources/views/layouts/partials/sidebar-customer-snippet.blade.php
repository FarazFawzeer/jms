{{-- Replace the existing "Customer" <li class="nav-item"> block in your sidebar with this one. --}}
<li class="nav-item">
    <a class="nav-link menu-arrow" href="#sidebarCustomer" data-bs-toggle="collapse" role="button"
        aria-expanded="{{ request()->routeIs('admin.customers.*') ? 'true' : 'false' }}" aria-controls="sidebarCustomer">
        <span class="nav-icon">
            <iconify-icon icon="solar:users-group-rounded-outline"></iconify-icon>
        </span>
        <span class="nav-text"> Customers </span>
    </a>
    <div class="collapse {{ request()->routeIs('admin.customers.*') ? 'show' : '' }}" id="sidebarCustomer">
        <ul class="nav sub-navbar-nav">
            <li class="sub-nav-item">
                <a class="sub-nav-link {{ request()->routeIs('admin.customers.create') ? 'active' : '' }}" href="{{ route('admin.customers.create') }}">New Customer</a>
            </li>
            <li class="sub-nav-item">
                <a class="sub-nav-link {{ request()->routeIs('admin.customers.index', 'admin.customers.edit') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">Customer List</a>
            </li>
        </ul>
    </div>
</li>
