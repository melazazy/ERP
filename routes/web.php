<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ReceivingForm;
use App\Livewire\RequisitionForm;
use App\Livewire\TrustForm;
use App\Livewire\ManagementItems;
use App\Livewire\ManagementUsers;
use App\Livewire\ManagementDepartments;
use App\Livewire\ManagementSuppliers;
use App\Livewire\ItemCard;
use App\Livewire\ManagementCategory;
use App\Livewire\ManagementSubcategory;
use App\Livewire\ReceivingSearch;
use App\Livewire\ExportReports;
use App\Livewire\BackupManager;
use App\Livewire\RequisitionSearch;
use App\Livewire\InventoryReports;
use App\Livewire\ItemMonitor;
use App\Livewire\ItemReport;
use App\Livewire\TransferForm;
use App\Livewire\DepartmentReport;
use App\Livewire\TrustSearch;
use App\Livewire\DocumentSearch;
use App\Livewire\SupplierReport;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\Actions\Logout;
use App\Http\Controllers\LocaleController;
use App\Livewire\TransactionDetails;
use App\Livewire\ManagementRoles;
use App\Http\Controllers\ProfileController;
use App\Livewire\ItemsReport;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| This file restores the original route structure to ensure compatibility
| with existing Blade files, while applying the necessary security fix
| by placing all protected routes inside the 'auth' middleware group.
*/

// Public Routes
Route::view('/', 'welcome');
Route::get('change', [LocaleController::class, 'change'])->name("lang.change");

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard - accessible by all authenticated users
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes - accessible by all authenticated users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Common Routes (All authenticated users)
    Route::get('item-card', ItemCard::class)->name('item-card');
    Route::get('document-search', DocumentSearch::class)->name('document-search');
    Route::get('/transaction/{type}/{number}', TransactionDetails::class)->name('transaction.details');
    
    // System Administrator Only Routes
    Route::middleware('role:System Administrator')->group(function () {
        Route::get('management-roles', ManagementRoles::class)->name('management.roles');
        Route::get('management-users', ManagementUsers::class)->name('management.users');
        Route::get('backup-manager', BackupManager::class)->name('backup-manager');
    });

    // Warehouse Management Routes (System Administrator, Warehouse Manager)
    Route::middleware('role:System Administrator,Warehouse Manager')->group(function () {
        Route::get('management-items', ManagementItems::class)->name('management.items');
        Route::get('management-category', ManagementCategory::class)->name('management-category');
        Route::get('management-subcategory', ManagementSubcategory::class)->name('management-subcategory');
        Route::get('management-departments', ManagementDepartments::class)->name('management.departments');
        Route::get('management-suppliers', ManagementSuppliers::class)->name('management.suppliers');
    });

    // Receiving Management Routes (System Administrator, Warehouse Manager, Receiving Clerk)
    Route::middleware('role:System Administrator,Warehouse Manager,Receiving Clerk')->group(function () {
        Route::get('receiving', ReceivingForm::class)->name('receiving');
        Route::get('receiving-search', ReceivingSearch::class)->name('receiving-search');
    });

    // Requisition Management Routes (System Administrator, Warehouse Manager, Requisition Clerk)
    Route::middleware('role:System Administrator,Warehouse Manager,Requisition Clerk')->group(function () {
        Route::get('requisition', RequisitionForm::class)->name('requisition');
        Route::get('requisition-search', RequisitionSearch::class)->name('requisition-search');
        Route::get('transfer', TransferForm::class)->name('transfer');
    });
    
    // Trust Management Routes (System Administrator, Warehouse Manager, Trust Clerk)
    Route::middleware('role:System Administrator,Warehouse Manager,Trust Clerk')->group(function () {
        Route::get('trusts', TrustForm::class)->name('trusts');
        Route::get('trust-search', TrustSearch::class)->name('trust-search');
    });

    // Inventory Management Routes (System Administrator, Warehouse Manager, Inventory Controller, Store Keeper)
    Route::middleware('role:System Administrator,Warehouse Manager,Inventory Controller,Store Keeper')->group(function () {
        Route::get('item-monitor', ItemMonitor::class)->name('item-monitor');
        Route::get('item-report', ItemReport::class)->name('item-report');
    });

    // Reports and Monitoring Routes (System Administrator, Warehouse Manager, Inventory Controller, Accountant, Auditor)
    Route::middleware('role:System Administrator,Warehouse Manager,Inventory Controller,Accountant,Auditor')->group(function () {
        Route::get('inventory-reports', InventoryReports::class)->name('inventory-reports');
        Route::get('department-reports', DepartmentReport::class)->name('department-reports');
        Route::get('supplier-reports', SupplierReport::class)->name('supplier-reports');
        Route::get('export-reports', ExportReports::class)->name('export-reports');
    });
    
    Route::get('items-report', ItemsReport::class)->name('items-report');
    
    // Department Management Routes (System Administrator, Department Manager)
    Route::middleware('role:System Administrator,Department Manager')->group(function () {
        Route::get('department-management', DepartmentReport::class)->name('department-management');
    });

    // Logout Route
    Route::get('logout', [Logout::class, '__invoke'])->name('logout');
});

require __DIR__.'/auth.php';