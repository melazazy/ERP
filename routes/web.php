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

Route::view('/', 'welcome');

// Public Routes
Route::get('receiving', ReceivingForm::class)->name('receiving');
Route::get('requisition', RequisitionForm::class)->name('requisition');
Route::get('transfer', TransferForm::class)->name('transfer');
Route::get('trusts', TrustForm::class)->name('trusts');
Route::get('item-card', ItemCard::class)->name('item-card');
Route::get('item-monitor', ItemMonitor::class)->name('item-monitor');
Route::get('item-report', ItemReport::class)->name('item-report');
Route::get('document-search', DocumentSearch::class)->name('document-search');
Route::get('receiving-search', ReceivingSearch::class)->name('receiving-search');
Route::get('export-reports', ExportReports::class)->name('export-reports');
Route::get('requisition-search', RequisitionSearch::class)->name('requisition-search');
Route::get('trust-search', TrustSearch::class)->name('trust-search');
Route::get('inventory-reports', InventoryReports::class)->name('inventory-reports');
Route::get('department-reports', DepartmentReport::class)->name('department-reports');
Route::get('supplier-reports', SupplierReport::class)->name('supplier-reports');
Route::get('management-items', ManagementItems::class)->name('management.items');
Route::get('management-users', ManagementUsers::class)->name('management.users');
Route::get('management-departments', ManagementDepartments::class)->name('management.departments');
Route::get('management-suppliers', ManagementSuppliers::class)->name('management.suppliers');
Route::get('/management-category', ManagementCategory::class)->name('management-category');
Route::get('/management-subcategory', ManagementSubcategory::class)->name('management-subcategory');
Route::get('backup-manager', BackupManager::class)->name('backup-manager');
Route::get('change', [LocaleController::class, 'change'])->name("lang.change");
Route::get('/transaction/{type}/{number}', App\Livewire\TransactionDetails::class)
    ->name('transaction.details');
// Authenticated Routes
// Logout
Route::get('logout', [Logout::class, '__invoke'])->name('logout');
// Route::post('logout', [Logout::class, '__invoke'])->name('logout');
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';