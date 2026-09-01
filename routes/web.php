<?php

use App\Http\Controllers\DependencyController;
use App\Http\Controllers\ProductWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [ProductWebController::class, 'index']
)->name('products.index');

Route::get(
    '/products/{product}/edit',
    [ProductWebController::class, 'edit']
)->name('products.edit');

Route::post(
    '/products',
    [ProductWebController::class, 'store']
)->name('products.store');

Route::put(
    '/products/{product}',
    [ProductWebController::class, 'update']
)->name('products.update');

Route::delete(
    '/products/{product}',
    [ProductWebController::class, 'destroy']
)->name('products.destroy');


/*
|--------------------------------------------------------------------------
| Dependency Manager
|--------------------------------------------------------------------------
*/

Route::get(
    '/dependencies',
    [DependencyController::class, 'index']
)->name('dependencies.index');


/*
|--------------------------------------------------------------------------
| Dependency Health Check
|--------------------------------------------------------------------------
*/

Route::get(
    '/dependencies/health-check',
    [DependencyController::class, 'check']
)->name('dependencies.check');


/*
|--------------------------------------------------------------------------
| Dependency Upgrade
|--------------------------------------------------------------------------
*/

Route::post(
    '/dependencies/{package}/upgrade',
    [DependencyController::class, 'upgrade']
)
    ->where('package', '.*')
    ->name('dependencies.upgrade');


/*
|--------------------------------------------------------------------------
| Export Dependency History
|--------------------------------------------------------------------------
*/

Route::get(
    '/dependencies/history/export',
    [DependencyController::class, 'exportHistory']
)->name('dependencies.history.export');


/*
|--------------------------------------------------------------------------
| Bulk Delete Dependency History
|--------------------------------------------------------------------------
*/

Route::delete(
    '/dependencies/history/bulk-delete',
    [DependencyController::class, 'bulkDelete']
)->name('dependencies.history.bulk-delete');


/*
|--------------------------------------------------------------------------
| Delete Single History
|--------------------------------------------------------------------------
*/

Route::delete(
    '/dependencies/history/{history}',
    [DependencyController::class, 'destroy']
)->name('dependencies.history.destroy');


/*
|--------------------------------------------------------------------------
| Clear All History
|--------------------------------------------------------------------------
*/

Route::delete(
    '/dependencies/history',
    [DependencyController::class, 'clearHistory']
)->name('dependencies.history.clear');