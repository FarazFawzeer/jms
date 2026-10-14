<?php

use App\Http\Controllers\RoutingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AccountEntryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SqlViewerController;

require __DIR__ . '/auth.php';

// Everything under /admin now needs a logged-in user
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    //admin
    Route::resource('users', UserController::class);

    //customer
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::get('customers/check-phone', [CustomerController::class, 'checkPhone'])->name('customers.check-phone');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::patch('customers/{customer}/status', [CustomerController::class, 'toggleStatus'])->name('customers.status');

    //supplier
    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::get('suppliers/check-phone', [SupplierController::class, 'checkPhone'])->name('suppliers.check-phone');
    Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::patch('suppliers/{supplier}/status', [SupplierController::class, 'toggleStatus'])->name('suppliers.status');

    //company (shared by all divisions)
    Route::get('company', [CompanyController::class, 'index'])->name('company.index');
    Route::put('company', [CompanyController::class, 'update'])->name('company.update');

    //divisions
    Route::get('divisions', [DivisionController::class, 'index'])->name('divisions.index');
    Route::get('divisions/create', [DivisionController::class, 'create'])->name('divisions.create');
    Route::post('divisions', [DivisionController::class, 'store'])->name('divisions.store');
    Route::get('divisions/{division}/edit', [DivisionController::class, 'edit'])->name('divisions.edit');
    Route::put('divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update');
    Route::patch('divisions/{division}/status', [DivisionController::class, 'toggleStatus'])->name('divisions.status');

    //item categories (shared by all divisions)
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::patch('categories/{category}/status', [CategoryController::class, 'toggleStatus'])->name('categories.status');

    //products / items (each item belongs to one division)
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('products/{product}/status', [ProductController::class, 'toggleStatus'])->name('products.status');

    //account types: income / expense heads (shared by all divisions)
    Route::get('account-types', [AccountTypeController::class, 'index'])->name('account-types.index');
    Route::get('account-types/create', [AccountTypeController::class, 'create'])->name('account-types.create');
    Route::post('account-types', [AccountTypeController::class, 'store'])->name('account-types.store');
    Route::get('account-types/{accountType}/edit', [AccountTypeController::class, 'edit'])->name('account-types.edit');
    Route::put('account-types/{accountType}', [AccountTypeController::class, 'update'])->name('account-types.update');
    Route::patch('account-types/{accountType}/status', [AccountTypeController::class, 'toggleStatus'])->name('account-types.status');

    //income & expense entries (each entry belongs to one division)
    Route::get('income-expenses', [AccountEntryController::class, 'index'])->name('income-expenses.index');
    Route::get('income-expenses/create', [AccountEntryController::class, 'create'])->name('income-expenses.create');
    Route::post('income-expenses', [AccountEntryController::class, 'store'])->name('income-expenses.store');
    Route::get('income-expenses/{entry}/edit', [AccountEntryController::class, 'edit'])->name('income-expenses.edit');
    Route::put('income-expenses/{entry}', [AccountEntryController::class, 'update'])->name('income-expenses.update');
    Route::patch('income-expenses/{entry}/status', [AccountEntryController::class, 'toggleStatus'])->name('income-expenses.status');

    //profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});

Route::get('/login', function () {
    return view('auth.signin');
})->name('login');

// Login action
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('index'); // create resources/views/dashboard.blade.php
    });
});
