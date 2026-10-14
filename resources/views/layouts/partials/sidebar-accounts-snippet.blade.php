{{-- Add these <li class="nav-item"> blocks right under the "Categories" item in your sidebar. --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}" href="{{ route('admin.income-expenses.index') }}">
        <span class="nav-icon">
            <iconify-icon icon="solar:wallet-money-outline"></iconify-icon>
        </span>
        <span class="nav-text"> Income &amp; Expenses </span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.account-types.*') ? 'active' : '' }}" href="{{ route('admin.account-types.index') }}">
        <span class="nav-icon">
            <iconify-icon icon="solar:bill-list-outline"></iconify-icon>
        </span>
        <span class="nav-text"> Account Types </span>
    </a>
</li>
