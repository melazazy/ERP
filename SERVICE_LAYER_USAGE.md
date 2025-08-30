# Service Layer Usage Guide

This document explains how to use the new service layer architecture in your ERP system.

## Overview

The service layer provides a clean separation of business logic from presentation logic, making your code more maintainable, testable, and reusable.

## Available Services

### 1. InventoryService
Handles all inventory-related operations including item management, stock calculations, and inventory reports.

### 2. ReceivingService
Manages goods receiving operations, including creation, updates, and automatic requisition generation.

### 3. RequisitionService
Handles requisition operations, including creation, updates, and stock validation.

### 4. TrustService
Manages trust-based allocations with stock availability checks.

### 5. TransferService
Handles inter-department item transfers with validation and availability checks.

### 6. ReportingService
Provides comprehensive reporting functionality for all system operations.

## Usage Examples

### In Livewire Components

#### Before (Old Way)
```php
class ManagementItems extends Component
{
    public function addItem()
    {
        // Validation logic here
        $this->validate([
            'newItem.name' => 'required|string|max:255',
            'newItem.code' => 'required|string|max:255|unique:items,code',
            'newItem.subcategory_id' => 'required|exists:subcategories,id',
        ]);

        // Business logic here
        Item::create([
            'name' => $this->newItem['name'],
            'code' => $this->newItem['code'],
            'subcategory_id' => $this->newItem['subcategory_id'],
        ]);

        $this->resetNewItem();
        $this->refreshItems();
    }
}
```

#### After (New Way)
```php
class ManagementItems extends Component
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function addItem()
    {
        try {
            $this->inventoryService->createItem($this->newItem);
            $this->resetNewItem();
            $this->refreshItems();
            session()->flash('message', 'Item created successfully.');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}
```

### Using Form Requests

#### Before (Old Way)
```php
class ManagementItems extends Component
{
    protected function rules()
    {
        return [
            'newItem.name' => 'required|string|max:255',
            'newItem.code' => 'required|string|max:255|unique:items,code,' . $this->newItem['id'],
            'newItem.subcategory_id' => 'required|exists:subcategories,id',
        ];
    }
}
```

#### After (New Way)
```php
use App\Http\Requests\ItemRequest;

class ManagementItems extends Component
{
    public function addItem(ItemRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $this->inventoryService->createItem($validatedData);
            $this->resetNewItem();
            $this->refreshItems();
            session()->flash('message', 'Item created successfully.');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}
```

## Service Method Examples

### InventoryService
```php
// Get items with quantities and filters
$items = $this->inventoryService->getItemsWithQuantities([
    'category' => 'Electronics',
    'search' => 'laptop'
]);

// Create new item
$item = $this->inventoryService->createItem([
    'name' => 'New Laptop',
    'code' => 'LAP001',
    'subcategory_id' => 1
]);

// Get item movements
$movements = $this->inventoryService->getItemMovements($itemId);

// Get inventory summary
$summary = $this->inventoryService->getInventorySummary();
```

### ReceivingService
```php
// Create receiving
$result = $this->receivingService->createReceiving([
    'receiving_number' => 'REC001',
    'supplier_id' => 1,
    'department_id' => 1,
    'date' => now()->toDateString(),
    'apply_tax' => true,
    'tax_rate' => 14,
    'create_requisition' => true
], $selectedItems);

// Search receiving
$receivingData = $this->receivingService->searchReceiving('REC001');

// Calculate totals
$totals = $this->receivingService->calculateTotals(
    $items,
    $applyTax = true,
    $applyDiscount = false,
    $taxRate = 14,
    $discountRate = 0
);
```

### RequisitionService
```php
// Create requisition
$requisitions = $this->requisitionService->createRequisition([
    'requisition_number' => 'REQ001',
    'department_id' => 1,
    'requested_by' => 1,
    'status' => 'pending'
], $selectedItems);

// Get requisitions with filters
$requisitions = $this->requisitionService->getRequisitions([
    'department' => 'IT',
    'status' => 'pending',
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31'
]);

// Get requisition summary
$summary = $this->requisitionService->getRequisitionSummary(
    '2024-01-01',
    '2024-01-31',
    1, // department_id
    'pending'
);
```

### TrustService
```php
// Create trust
$trusts = $this->trustService->createTrust([
    'requisition_number' => 'TRU001',
    'department_id' => 1,
    'requested_by' => 1,
    'status' => 'pending'
], $selectedItems);

// Check item availability
$isAvailable = $this->trustService->isItemAvailable($itemId, $quantity);

// Get trust history for item
$history = $this->trustService->getItemTrustHistory($itemId);
```

### TransferService
```php
// Transfer items
$result = $this->transferService->transferItems(
    $fromDepartmentId = 1,
    $toDepartmentId = 2,
    $selectedItems
);

// Validate transfer
$validation = $this->transferService->validateTransfer(
    $fromDepartmentId,
    $toDepartmentId,
    $selectedItems
);

// Get transfer history
$history = $this->transferService->getTransferHistory([
    'from_department' => 1,
    'to_department' => 2,
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31'
]);
```

### ReportingService
```php
// Get inventory report
$report = $this->reportingService->getInventoryReport([
    'search' => 'laptop',
    'category' => 'Electronics',
    'per_page' => 20
]);

// Get department report
$deptReport = $this->reportingService->getDepartmentReport(
    $departmentId = 1,
    $dateFrom = '2024-01-01',
    $dateTo = '2024-01-31',
    $docType = 'all'
);

// Get supplier report
$supplierReport = $this->reportingService->getSupplierReport(
    $supplierId = 1,
    $dateFrom = '2024-01-01',
    $dateTo = '2024-01-31'
);

// Get dashboard stats
$stats = $this->reportingService->getDashboardStats('Warehouse Manager');
```

## Error Handling

All services use the BaseService class which provides consistent error handling:

```php
try {
    $result = $this->inventoryService->createItem($itemData);
    session()->flash('message', 'Item created successfully.');
} catch (InsufficientStockException $e) {
    session()->flash('error', 'Insufficient stock: ' . $e->getMessage());
    \Log::warning('Stock issue', $e->getContext());
} catch (DuplicateDocumentException $e) {
    session()->flash('error', 'Document already exists: ' . $e->getMessage());
    \Log::warning('Duplicate document', $e->getContext());
} catch (Exception $e) {
    session()->flash('error', 'An error occurred: ' . $e->getMessage());
    \Log::error('Unexpected error', ['error' => $e->getMessage()]);
}
```

## Benefits

1. **Separation of Concerns**: Business logic is separated from presentation logic
2. **Reusability**: Services can be used across different components and controllers
3. **Testability**: Services can be easily unit tested
4. **Maintainability**: Changes to business logic only need to be made in one place
5. **Consistency**: All operations follow the same patterns and error handling
6. **Logging**: Automatic logging of all service operations
7. **Transaction Management**: Automatic database transaction handling with rollback on errors

## Migration Steps

1. **Update Livewire Components**: Inject services and replace business logic with service calls
2. **Use Form Requests**: Replace inline validation with form request classes
3. **Update Error Handling**: Use try-catch blocks with custom exceptions
4. **Test Services**: Create unit tests for all service methods
5. **Update Documentation**: Document any new service methods or changes

## Testing

Example test for InventoryService:

```php
class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
    }

    public function test_can_create_item()
    {
        $itemData = [
            'name' => 'Test Item',
            'code' => 'TEST001',
            'subcategory_id' => 1
        ];

        $item = $this->service->createItem($itemData);

        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('Test Item', $item->name);
        $this->assertEquals('TEST001', $item->code);
    }
}
```

This service layer architecture provides a solid foundation for your ERP system's continued growth and maintenance.
