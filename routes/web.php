<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', [PublicMenuController::class, 'home'])->name('home');
Route::get('/menu', [PublicMenuController::class, 'menu'])->name('public.menu');
Route::get('/menu/{menu}', [PublicMenuController::class, 'show'])->name('public.menu.show');
Route::post('/menu/{menu}/order', [PublicMenuController::class, 'order'])->name('public.menu.order');
Route::get('/about', [PublicMenuController::class, 'about'])->name('about');

Route::middleware(['auth', 'role:admin,owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stock/history', [StockController::class, 'history'])->name('stock.history');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('items', ItemController::class)->except('show');
    Route::resource('menu-categories', MenuCategoryController::class)->except('show');
    Route::resource('menus', MenuController::class)->except('show');

    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('/stock/in', [StockController::class, 'inForm'])->name('stock.in.form');
    Route::post('/stock/in', [StockController::class, 'storeIn'])->name('stock.in.store');
    Route::get('/stock/out', [StockController::class, 'outForm'])->name('stock.out.form');
    Route::post('/stock/out', [StockController::class, 'storeOut'])->name('stock.out.store');
    Route::get('/stock/adjustment', [StockController::class, 'adjustmentForm'])->name('stock.adjustment.form');
    Route::post('/stock/adjustment', [StockController::class, 'storeAdjustment'])->name('stock.adjustment.store');
});
