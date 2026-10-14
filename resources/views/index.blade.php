@extends('layouts.vertical', ['subtitle' => 'Dashboard'])

@section('content')

@include('layouts.partials.page-title', ['title' => 'JMS', 'subtitle' => 'Dashboard'])

@php
    $user = auth()->user();
    $canManage = $user?->canManageSystem();

    // Quick links to the finished modules. 'admin' => true means only Overall-division admins see it.
    $modules = [
        ['route' => 'admin.customers.index',       'title' => 'Customers',          'text' => 'Register and manage customers',   'icon' => 'solar:users-group-rounded-outline', 'color' => 'primary'],
        ['route' => 'admin.suppliers.index',       'title' => 'Suppliers',          'text' => 'Register and manage suppliers',   'icon' => 'solar:delivery-outline',            'color' => 'success'],
        ['route' => 'admin.products.index',        'title' => 'Products',           'text' => 'Items held in stock',             'icon' => 'solar:tag-price-outline',           'color' => 'warning'],
        ['route' => 'admin.categories.index',      'title' => 'Categories',         'text' => 'Item categories',                 'icon' => 'solar:widget-4-outline',            'color' => 'info'],
        ['route' => 'admin.income-expenses.index', 'title' => 'Income & Expenses',  'text' => 'Record income and expenses',      'icon' => 'solar:wallet-money-outline',        'color' => 'success'],
        ['route' => 'admin.account-types.index',   'title' => 'Account Types',      'text' => 'Income and expense heads',        'icon' => 'solar:bill-list-outline',           'color' => 'secondary'],
        ['route' => 'admin.company.index',         'title' => 'Company',            'text' => 'Company details',                 'icon' => 'solar:shop-2-outline',              'color' => 'danger',  'admin' => true],
        ['route' => 'admin.divisions.index',       'title' => 'Divisions',          'text' => 'Branches and stock locations',    'icon' => 'solar:buildings-2-outline',         'color' => 'primary', 'admin' => true],
        ['route' => 'admin.users.index',           'title' => 'Users',              'text' => 'System users and access',         'icon' => 'solar:user-circle-outline',         'color' => 'dark',    'admin' => true],
    ];

    $modules = array_filter($modules, fn ($m) => empty($m['admin']) || $canManage);
@endphp

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-md bg-primary bg-opacity-10 rounded-circle flex-shrink-0">
                        <iconify-icon icon="solar:crown-outline" class="fs-32 text-primary avatar-title"></iconify-icon>
                    </div>
                    <div>
                        <h4 class="mb-1">Welcome, {{ $user?->name }}</h4>
                        <p class="text-muted mb-0">
                            Jewellery Management System
                            <span class="badge badge-soft-primary ms-1">{{ $user?->division_name }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach ($modules as $module)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route($module['route']) }}" class="card text-reset">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-md bg-{{ $module['color'] }} bg-opacity-10 rounded-circle flex-shrink-0">
                            <iconify-icon icon="{{ $module['icon'] }}" class="fs-32 text-{{ $module['color'] }} avatar-title"></iconify-icon>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h5 class="text-dark mb-1">{{ $module['title'] }}</h5>
                            <p class="text-muted mb-0 text-truncate">{{ $module['text'] }}</p>
                        </div>
                        <i class="bx bx-right-arrow-alt fs-20 text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-md bg-info bg-opacity-10 rounded-circle flex-shrink-0">
                        <iconify-icon icon="solar:chart-2-outline" class="fs-32 text-info avatar-title"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="mb-1">Analytics dashboard coming soon</h5>
                        <p class="text-muted mb-0">Sales, stock and income summaries for each division will appear here once those modules are complete.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
