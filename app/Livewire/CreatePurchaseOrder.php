<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JobOrder;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatePurchaseOrder extends Component
{
    public $jobOrders = [];
    public $selectedJobOrderIds = [];
    public $selectedSupplierId = null;
    public $poDate;
    public $notes = '';
    
    public $availableItems = [];
    public $selectedItems = [];
    public $itemsBySupplier = [];
    public $selectedSupplierItems = [];
    
    public $suppliers = [];
    public $multipleSuppliersDetected = false;
    public $availableSuppliers = [];
    public $currentSelectedSupplierId = null;
    
    public $showJobOrderSelection = true;
    public $showItemSelection = false;
    public $showSupplierSelection = false;
    public $showFinalReview = false;
    
    public $searchItem = '';
    public $purchasedItemsDetails = [];
    public $currentSupplierCurrency = 'USD'; // Default currency

    protected $rules = [
        'selectedJobOrderIds' => 'required|array|min:1',
        'selectedJobOrderIds.*' => 'exists:job_orders,id',
        'poDate' => 'required|date',
        'notes' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        $this->poDate = now()->format('Y-m-d');
        $this->loadJobOrders();
        $this->loadSuppliers();
        
        // Pre-select job order if passed in URL
        $jobOrderId = request()->query('job_order');
        if ($jobOrderId) {
            $this->selectedJobOrderIds = [$jobOrderId];
            $this->loadAvailableItems();
            $this->showJobOrderSelection = false;
            $this->showItemSelection = true;
        }
    }

    public function loadJobOrders()
    {
        $this->jobOrders = JobOrder::with(['supplier', 'boxes.supplier', 'dividers.supplier', 'customer'])
            ->where('status', '!=', 'completed')
            ->where(function($query) {
                $query->whereHas('boxes', function($q) {
                    $q->where('order_qty', '>', 0);
                })
                ->orWhereHas('dividers', function($q) {
                    $q->where('quantity', '>', 0);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        Log::info('Loaded job orders', [
            'count' => $this->jobOrders->count(),
            'job_orders' => $this->jobOrders->map(function($jo) {
                return [
                    'id' => $jo->id,
                    'job_number' => $jo->job_number,
                    'job_order_number' => $jo->job_order_number,
                    'supplier_id' => $jo->supplier_id
                ];
            })->toArray()
        ]);
    }

    public function loadSuppliers()
    {
        $this->suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function toggleJobOrder($jobOrderId)
    {
        if (in_array($jobOrderId, $this->selectedJobOrderIds)) {
            // Remove from selection
            $this->selectedJobOrderIds = array_filter($this->selectedJobOrderIds, function($id) use ($jobOrderId) {
                return $id != $jobOrderId;
            });
            
            // Reset current supplier if no job orders selected
            if (empty($this->selectedJobOrderIds)) {
                $this->currentSelectedSupplierId = null;
            }
        } else {
            // Check if this job order has a different supplier
            $jobOrder = $this->jobOrders->find($jobOrderId);
            $jobOrderSupplierId = $jobOrder->supplier_id;
            
            // If we already have selected job orders, check supplier consistency
            if (!empty($this->selectedJobOrderIds) && $this->currentSelectedSupplierId !== null) {
                if ($jobOrderSupplierId !== $this->currentSelectedSupplierId) {
                    // Different supplier - show error message
                    $currentSupplierName = $this->getSupplierName($this->currentSelectedSupplierId);
                    $newSupplierName = $this->getSupplierName($jobOrderSupplierId);
                    
                    session()->flash('error', "Cannot select job orders from different suppliers. Current selection is from '{$currentSupplierName}'. The selected job order is from '{$newSupplierName}'. Please select job orders from the same supplier only.");
                    return;
                }
            }
            
            // Add to selection
            $this->selectedJobOrderIds[] = $jobOrderId;
            
            // Set the current supplier if this is the first selection
            if ($this->currentSelectedSupplierId === null) {
                $this->currentSelectedSupplierId = $jobOrderSupplierId;
            }
        }
        
        // Reload available items when selection changes
        if (!empty($this->selectedJobOrderIds)) {
            $this->loadAvailableItems();
        }
    }
    
    public function proceedToItemSelection()
    {
        if (empty($this->selectedJobOrderIds)) {
            session()->flash('error', 'Please select at least one job order.');
            return;
        }
        
        $this->loadAvailableItems();
        $this->selectedItems = []; // Reset selected items
        $this->showJobOrderSelection = false;
        $this->showItemSelection = true;
    }
    
    public function addItem($itemId)
    {
        Log::info('Adding item', [
            'item_id' => $itemId,
            'current_selected_count' => count($this->selectedItems)
        ]);
        
        foreach ($this->availableItems as $item) {
            if ($item['id'] === $itemId) {
                // Validate that item has available quantity
                if ($item['remaining_qty'] <= 0) {
                    session()->flash('error', "Cannot add {$item['description']} - no available quantity remaining.");
                    return;
                }
                
                // Check if item already exists in selected items
                $existingItemIndex = null;
                foreach ($this->selectedItems as $index => $selectedItem) {
                    if ($selectedItem['id'] === $itemId) {
                        $existingItemIndex = $index;
                        break;
                    }
                }
                
                if ($existingItemIndex !== null) {
                    // Item already exists, increase purchase quantity if possible
                    $currentPurchaseQty = $this->selectedItems[$existingItemIndex]['purchase_qty'] ?? $this->selectedItems[$existingItemIndex]['available_qty'];
                    $availableQty = $this->selectedItems[$existingItemIndex]['available_qty'];
                    
                    if ($currentPurchaseQty < $availableQty) {
                        // Increase by 1 or to max available
                        $newQty = min($currentPurchaseQty + 1, $availableQty);
                        $this->selectedItems[$existingItemIndex]['purchase_qty'] = $newQty;
                        
                        if ($newQty >= $availableQty) {
                            session()->flash('info', "Maximum available quantity ({$availableQty}) selected for {$item['description']}.");
                        }
                    } else {
                        session()->flash('warning', "Maximum available quantity ({$availableQty}) already selected for {$item['description']}.");
                    }
                } else {
                    // Item doesn't exist, add it with full available quantity as default
                    // For BOX items, board_qty is already calculated as order_qty / no_of_ups
                    // For DIVIDER items, board_qty is the quantity from database
                    $boardQty = isset($item['board_qty']) ? $item['board_qty'] : $item['remaining_qty'];
                    
                    // purchase_qty is what's actually being purchased - should not exceed available_qty
                    // For BOX items: board_qty shows calculated value (order_qty / no_of_ups) for display only
                    // For DIVIDER items: board_qty shows database quantity for display only
                    // But purchase_qty should always be capped to available_qty
                    $purchaseQty = min($item['remaining_qty'], ($item['type'] === 'BOX') ? $boardQty : $item['remaining_qty']);
                    
                    $this->selectedItems[] = [
                        'id' => $item['id'],
                        'type' => $item['type'],
                        'item_id' => $item['item_id'],
                        'description' => $item['description'],
                        'unit' => $item['unit'],
                        'available_qty' => $item['remaining_qty'],
                        'board_qty' => $boardQty, // For BOX: calculated as order_qty/no_of_ups; For DIVIDER: quantity from database (display only)
                        'purchase_qty' => $purchaseQty, // Purchase quantity (what's actually being purchased)
                        'supplier_id' => $item['supplier_id'],
                        'unit_cost' => $item['unit_cost'],
                        'original_number' => $item['original_number'], // Store the original item number
                    ];
                    
                    session()->flash('success', "Added {$item['description']} with full available quantity ({$item['remaining_qty']}).");
                }
                
                Log::info('Item added successfully', [
                    'new_selected_count' => count($this->selectedItems),
                    'selected_items' => $this->selectedItems
                ]);
                break;
            }
        }
    }
    
    public function removeItem($itemId)
    {
        $this->selectedItems = array_filter($this->selectedItems, function($item) use ($itemId) {
            return $item['id'] !== $itemId;
        });
    }
    
    public function updateSelectedQuantity($itemId, $quantity)
    {
        foreach ($this->selectedItems as &$item) {
            if ($item['id'] === $itemId) {
                // Validate quantity input
                if ($quantity < 1) {
                    session()->flash('error', "Quantity must be at least 1 for {$item['description']}.");
                    return;
                }
                
                if ($quantity > $item['available_qty']) {
                    session()->flash('error', "Cannot select {$quantity} items for {$item['description']}. Maximum available: {$item['available_qty']}.");
                    return;
                }
                
                // Update purchase quantity (what's actually being purchased)
                // board_qty is for display only (calculated for BOX, database quantity for DIVIDER)
                $item['purchase_qty'] = $quantity;
                session()->flash('success', "Updated purchase quantity for {$item['description']} to {$quantity}.");
                break;
            }
        }
    }
    
    public function getFilteredAvailableItems()
    {
        if (empty($this->searchItem)) {
            return $this->availableItems; // Return all items when no search
        }
        
        $searchTerm = strtolower($this->searchItem);
        
        return array_filter($this->availableItems, function($item) use ($searchTerm) {
            // Search in description (dimensions)
            if (stripos($item['description'], $searchTerm) !== false) {
                return true;
            }
            
            // Search in job order number
            if (stripos($item['job_order_number'], $searchTerm) !== false) {
                return true;
            }
            
            // Search in dimensions (extract numbers from description)
            if (preg_match('/\d+/', $searchTerm)) {
                // If search term contains numbers, check if they match dimensions
                $description = strtolower($item['description']);
                if (strpos($description, $searchTerm) !== false) {
                    return true;
                }
            }
            
            // Search in unit (MM, CM, INCHES)
            if (stripos($item['unit'], $searchTerm) !== false) {
                return true;
            }
            
            return false;
        });
    }
    
    public function updatedSearchItem()
    {
        // This method is called automatically when searchItem is updated
        // The filtered results will be shown in real-time
    }

    public function loadAvailableItems()
    {
        $jobOrders = JobOrder::with(['boxes', 'dividers', 'supplier'])->whereIn('id', $this->selectedJobOrderIds)->get();
        
        $this->availableItems = [];
        $itemCounter = 1; // Start numbering from 1
        
        // Set the current supplier's currency
        if ($jobOrders->isNotEmpty()) {
            $firstJobOrder = $jobOrders->first();
            if ($firstJobOrder->supplier) {
                $this->currentSupplierCurrency = $firstJobOrder->supplier->currency ?? 'USD';
            }
        }
        
        Log::info('Loading available items', [
            'selected_job_order_ids' => $this->selectedJobOrderIds,
            'job_orders_count' => $jobOrders->count(),
            'current_supplier_currency' => $this->currentSupplierCurrency
        ]);
        
        foreach ($jobOrders as $jobOrder) {
            Log::info('Processing job order', [
                'job_order_id' => $jobOrder->id,
                'job_order_number' => $jobOrder->job_order_number,
                'boxes_count' => $jobOrder->boxes->count(),
                'dividers_count' => $jobOrder->dividers->count()
            ]);
            
            // Add boxes with remaining quantities
            foreach ($jobOrder->boxes as $box) {
                $remainingQty = $this->calculateRemainingQuantity($box);
                Log::info('Box details', [
                    'box_id' => $box->id,
                    'order_qty' => $box->order_qty,
                    'remaining_qty' => $remainingQty
                ]);
                
                if ($remainingQty > 0) {
                    // Calculate board_qty as order_qty / no_of_ups for BOX items
                    $boardQty = ($box->no_of_ups && $box->no_of_ups > 0) 
                        ? ($box->order_qty / $box->no_of_ups) 
                        : 0;
                    
                    $this->availableItems[] = [
                        'id' => 'box_' . $box->id,
                        'type' => 'BOX',
                        'item_id' => $box->id,
                        'job_order_id' => $jobOrder->id,
                        'job_order_number' => $jobOrder->job_order_number ?: $jobOrder->job_number,
                        'description' => "BOX - {$box->length} x {$box->width} x {$box->height} {$box->unit}",
                        'unit' => $box->unit,
                        'order_qty' => $box->order_qty,
                        'remaining_qty' => $remainingQty,
                        'board_qty' => $boardQty, // Calculated as order_qty / no_of_ups
                        'supplier_id' => $box->supplier_id,
                        'unit_cost' => $box->supplier_price ?? 0,
                        'original_number' => $itemCounter, // Store the original item number
                    ];
                    $itemCounter++;
                }
            }
            
            // Add dividers with remaining quantities
            foreach ($jobOrder->dividers as $divider) {
                $remainingQty = $this->calculateRemainingQuantity($divider);
                Log::info('Divider details', [
                    'divider_id' => $divider->id,
                    'quantity' => $divider->quantity,
                    'remaining_qty' => $remainingQty
                ]);
                
                if ($remainingQty > 0) {
                    $this->availableItems[] = [
                        'id' => 'divider_' . $divider->id,
                        'type' => 'DIVIDER',
                        'item_id' => $divider->id,
                        'job_order_id' => $jobOrder->id,
                        'job_order_number' => $jobOrder->job_order_number ?: $jobOrder->job_number,
                        'description' => "DIVIDER - {$divider->combination_1} {$divider->unit}",
                        'unit' => $divider->unit,
                        'order_qty' => $divider->quantity,
                        'remaining_qty' => $remainingQty,
                        'board_qty' => $divider->quantity, // For DIVIDER, use quantity field from database
                        'supplier_id' => null, // Dividers might not have supplier_id
                        'unit_cost' => $divider->supplier_price ?? 0,
                        'original_number' => $itemCounter, // Store the original item number
                    ];
                    $itemCounter++;
                }
            }
        }
        
        Log::info('Final available items count', ['count' => count($this->availableItems)]);
        
        // If no items available, provide detailed information about why
        if (empty($this->availableItems)) {
            $this->logUnavailableItems($jobOrders);
            $this->loadPurchasedItemsDetails($jobOrders);
        }
    }

    private function calculateRemainingQuantity($item)
    {
        // Calculate remaining quantity by subtracting already purchased quantities
        // EXCLUDE cancelled purchase orders from the calculation
        $purchasedQty = PurchaseOrderItem::where('item_type', $item instanceof JobOrderBox ? 'box' : 'divider')
            ->where('item_id', $item->id)
            ->whereHas('purchaseOrder', function($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->sum('quantity');
            
        // Use correct column name based on item type
        $originalQty = $item instanceof JobOrderBox ? $item->order_qty : $item->quantity;
        return max(0, $originalQty - $purchasedQty);
    }

    private function logUnavailableItems($jobOrders)
    {
        Log::info('No available items found. Analyzing job orders...');
        
        foreach ($jobOrders as $jobOrder) {
            Log::info('Job Order Analysis', [
                'job_order_id' => $jobOrder->id,
                'job_order_number' => $jobOrder->job_order_number ?: $jobOrder->job_number,
                'boxes_count' => $jobOrder->boxes->count(),
                'dividers_count' => $jobOrder->dividers->count()
            ]);
            
            // Check boxes
            foreach ($jobOrder->boxes as $box) {
                $purchasedQty = PurchaseOrderItem::where('item_type', 'box')
                    ->where('item_id', $box->id)
                    ->sum('quantity');
                $remaining = max(0, $box->order_qty - $purchasedQty);
                
                // Get purchase order details for this box
                $purchaseOrders = PurchaseOrderItem::with('purchaseOrder')
                    ->where('item_type', 'box')
                    ->where('item_id', $box->id)
                    ->get();
                
                Log::info('Box Analysis', [
                    'box_id' => $box->id,
                    'order_qty' => $box->order_qty,
                    'purchased_qty' => $purchasedQty,
                    'remaining_qty' => $remaining,
                    'status' => $remaining > 0 ? 'Available' : 'Fully Purchased',
                    'purchase_orders' => $purchaseOrders->map(function($poItem) {
                        return [
                            'po_number' => $poItem->purchaseOrder->po_number,
                            'quantity' => $poItem->quantity,
                            'created_at' => $poItem->created_at
                        ];
                    })->toArray()
                ]);
            }
            
            // Check dividers
            foreach ($jobOrder->dividers as $divider) {
                $purchasedQty = PurchaseOrderItem::where('item_type', 'divider')
                    ->where('item_id', $divider->id)
                    ->sum('quantity');
                $remaining = max(0, $divider->quantity - $purchasedQty);
                
                // Get purchase order details for this divider
                $purchaseOrders = PurchaseOrderItem::with('purchaseOrder')
                    ->where('item_type', 'divider')
                    ->where('item_id', $divider->id)
                    ->get();
                
                Log::info('Divider Analysis', [
                    'divider_id' => $divider->id,
                    'quantity' => $divider->quantity,
                    'purchased_qty' => $purchasedQty,
                    'remaining_qty' => $remaining,
                    'status' => $remaining > 0 ? 'Available' : 'Fully Purchased',
                    'purchase_orders' => $purchaseOrders->map(function($poItem) {
                        return [
                            'po_number' => $poItem->purchaseOrder->po_number,
                            'quantity' => $poItem->quantity,
                            'created_at' => $poItem->created_at
                        ];
                    })->toArray()
                ]);
            }
        }
    }

    private function loadPurchasedItemsDetails($jobOrders)
    {
        $this->purchasedItemsDetails = [];
        
        foreach ($jobOrders as $jobOrder) {
            $jobOrderDetails = [
                'job_order_number' => $jobOrder->job_order_number ?: $jobOrder->job_number,
                'items' => []
            ];
            
            // Check boxes
            foreach ($jobOrder->boxes as $box) {
                $purchasedQty = PurchaseOrderItem::where('item_type', 'box')
                    ->where('item_id', $box->id)
                    ->whereHas('purchaseOrder', function($query) {
                        $query->where('status', '!=', 'cancelled');
                    })
                    ->sum('quantity');
                
                if ($purchasedQty > 0) {
                    $purchaseOrders = PurchaseOrderItem::with('purchaseOrder')
                        ->where('item_type', 'box')
                        ->where('item_id', $box->id)
                        ->whereHas('purchaseOrder', function($query) {
                            $query->where('status', '!=', 'cancelled');
                        })
                        ->get();
                    
                    $jobOrderDetails['items'][] = [
                        'type' => 'BOX',
                        'description' => "BOX - {$box->length} x {$box->width} x {$box->height} {$box->unit}",
                        'order_qty' => $box->order_qty,
                        'purchased_qty' => $purchasedQty,
                        'remaining_qty' => max(0, $box->order_qty - $purchasedQty),
                        'purchase_orders' => $purchaseOrders->map(function($poItem) {
                            return [
                                'po_number' => $poItem->purchaseOrder->po_number,
                                'quantity' => $poItem->quantity,
                                'created_at' => $poItem->created_at->format('Y-m-d H:i:s')
                            ];
                        })->toArray()
                    ];
                }
            }
            
            // Check dividers
            foreach ($jobOrder->dividers as $divider) {
                $purchasedQty = PurchaseOrderItem::where('item_type', 'divider')
                    ->where('item_id', $divider->id)
                    ->whereHas('purchaseOrder', function($query) {
                        $query->where('status', '!=', 'cancelled');
                    })
                    ->sum('quantity');
                
                if ($purchasedQty > 0) {
                    $purchaseOrders = PurchaseOrderItem::with('purchaseOrder')
                        ->where('item_type', 'divider')
                        ->where('item_id', $divider->id)
                        ->whereHas('purchaseOrder', function($query) {
                            $query->where('status', '!=', 'cancelled');
                        })
                        ->get();
                    
                    $jobOrderDetails['items'][] = [
                        'type' => 'DIVIDER',
                        'description' => "DIVIDER - {$divider->combination_1} {$divider->unit}",
                        'order_qty' => $divider->quantity,
                        'purchased_qty' => $purchasedQty,
                        'remaining_qty' => max(0, $divider->quantity - $purchasedQty),
                        'purchase_orders' => $purchaseOrders->map(function($poItem) {
                            return [
                                'po_number' => $poItem->purchaseOrder->po_number,
                                'quantity' => $poItem->quantity,
                                'created_at' => $poItem->created_at->format('Y-m-d H:i:s')
                            ];
                        })->toArray()
                    ];
                }
            }
            
            if (!empty($jobOrderDetails['items'])) {
                $this->purchasedItemsDetails[] = $jobOrderDetails;
            }
        }
    }

    public function getCurrencySymbol()
    {
        return match($this->currentSupplierCurrency) {
            'LKR' => 'Rs.',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => '$'
        };
    }
    
    public function validateQuantities()
    {
        $errors = [];
        $warnings = [];
        
        foreach ($this->selectedItems as $item) {
            // Only validate purchase_qty (what's actually being purchased)
            // board_qty is display-only (calculated for BOX, database quantity for DIVIDER)
            $purchaseQty = $item['purchase_qty'] ?? 0;
            
            if ($purchaseQty > $item['available_qty']) {
                $errors[] = "{$item['description']}: Purchase quantity ({$purchaseQty}) exceeds available quantity ({$item['available_qty']}).";
            }
            if ($purchaseQty < 1) {
                $errors[] = "{$item['description']}: Purchase quantity must be at least 1.";
            }
            if ($purchaseQty == $item['available_qty'] && $item['available_qty'] > 0) {
                $warnings[] = "{$item['description']}: All available quantity ({$item['available_qty']}) is being purchased.";
            }
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'is_valid' => empty($errors)
        ];
    }

    public function resetPurchaseHistory()
    {
        try {
            // Delete all purchase order items
            PurchaseOrderItem::truncate();
            
            // Delete all purchase orders
            PurchaseOrder::truncate();
            
            // Delete all GRNs and GRN items
            DB::table('grn_items')->truncate();
            DB::table('grns')->truncate();
            
            // Delete all inventory transactions
            DB::table('inventory_transactions')->truncate();
            
            session()->flash('success', 'Purchase history has been reset. You can now create purchase orders for the same items.');
            
            // Reload available items
            $this->loadAvailableItems();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error resetting purchase history: ' . $e->getMessage());
        }
    }

    public function toggleItemSelection($itemId)
    {
        foreach ($this->availableItems as &$item) {
            if ($item['id'] === $itemId) {
                // This method is not currently used, but if it is, we should handle purchase_qty
                // For now, keeping the original logic
                if (isset($item['purchase_qty']) && $item['purchase_qty'] > 0) {
                    $item['purchase_qty'] = 0;
                } else {
                    $item['purchase_qty'] = $item['remaining_qty'];
                }
                break;
            }
        }
    }


    public function proceedToFinalReview()
    {
        Log::info('Proceeding to final review', [
            'selected_items_count' => count($this->selectedItems),
            'selected_items' => $this->selectedItems,
            'selected_job_order_ids' => $this->selectedJobOrderIds,
            'current_selected_supplier_id' => $this->currentSelectedSupplierId
        ]);
        
        // Check if we have selected items
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Please select at least one item to add to the purchase order.');
            return;
        }
        
        // Determine the supplier from the selected items
        $this->determineSupplierFromItems();
        
        $this->showItemSelection = false;
        $this->showFinalReview = true;
        
        Log::info('After transition to final review', [
            'show_item_selection' => $this->showItemSelection,
            'show_final_review' => $this->showFinalReview,
            'selected_supplier_id' => $this->selectedSupplierId,
            'current_selected_supplier_id' => $this->currentSelectedSupplierId,
            'job_orders_count' => $this->jobOrders->count(),
            'job_orders_data' => $this->jobOrders->pluck('job_order_number', 'id')->toArray()
        ]);
    }

    public function determineSupplierFromItems()
    {
        // Group items by supplier
        $this->itemsBySupplier = [];
        $this->availableSuppliers = [];
        
        foreach ($this->selectedItems as $item) {
            $supplierId = $item['supplier_id'];
            $supplierName = $this->getSupplierName($supplierId);
            
            if (!isset($this->itemsBySupplier[$supplierId])) {
                $this->itemsBySupplier[$supplierId] = [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $supplierName,
                    'items' => []
                ];
                $this->availableSuppliers[] = [
                    'id' => $supplierId,
                    'name' => $supplierName
                ];
            }
            
            $this->itemsBySupplier[$supplierId]['items'][] = $item;
        }
        
        $supplierCount = count($this->availableSuppliers);
        
        Log::info('Determining supplier from items', [
            'supplier_count' => $supplierCount,
            'available_suppliers' => $this->availableSuppliers,
            'items_by_supplier' => $this->itemsBySupplier
        ]);
        
        if ($supplierCount === 1) {
            // All items have the same supplier
            $this->selectedSupplierId = $this->availableSuppliers[0]['id'];
            $this->multipleSuppliersDetected = false;
            $this->selectedSupplierItems = $this->selectedItems;
        } elseif ($supplierCount > 1) {
            // Multiple suppliers detected
            $this->multipleSuppliersDetected = true;
            $this->selectedSupplierId = null;
            $this->selectedSupplierItems = [];
            session()->flash('warning', 'Items from ' . $supplierCount . ' different suppliers detected. Please select which supplier to use for this purchase order.');
        } else {
            // No supplier found
            $this->selectedSupplierId = null;
            $this->multipleSuppliersDetected = false;
            session()->flash('error', 'No supplier found for the selected items.');
        }
    }
    
    private function getSupplierName($supplierId)
    {
        if (!$supplierId) return 'No Supplier';
        
        $supplier = $this->suppliers->find($supplierId);
        return $supplier ? $supplier->name : 'Unknown Supplier';
    }
    
    public function selectSupplier($supplierId)
    {
        $this->selectedSupplierId = $supplierId;
        
        // Filter items for the selected supplier
        $this->selectedSupplierItems = collect($this->selectedItems)
            ->filter(function($item) use ($supplierId) {
                return $item['supplier_id'] == $supplierId;
            })
            ->values()
            ->toArray();
        
        Log::info('Supplier selected', [
            'selected_supplier_id' => $supplierId,
            'filtered_items_count' => count($this->selectedSupplierItems)
        ]);
        
        session()->flash('success', 'Supplier selected. ' . count($this->selectedSupplierItems) . ' items will be included in this purchase order.');
    }
    
    public function createSeparatePurchaseOrders()
    {
        // This method would create separate purchase orders for each supplier
        // For now, we'll just show a message
        session()->flash('info', 'Feature to create separate purchase orders for each supplier will be implemented in the next update.');
    }

    public function createPurchaseOrder()
    {
        Log::info('Create purchase order button clicked', [
            'selected_job_order_ids' => $this->selectedJobOrderIds,
            'current_selected_supplier_id' => $this->currentSelectedSupplierId,
            'selected_supplier_id' => $this->selectedSupplierId,
            'multiple_suppliers_detected' => $this->multipleSuppliersDetected,
            'selected_items_count' => count($this->selectedItems),
            'po_date' => $this->poDate
        ]);
        
        // Manual validation since rules structure changed
        if (empty($this->selectedJobOrderIds)) {
            session()->flash('error', 'Please select at least one job order.');
            return;
        }
        
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Please select at least one item.');
            return;
        }
        
        // Final validation: Check that all selected quantities are within available limits
        $validationErrors = [];
        foreach ($this->selectedItems as $item) {
            $purchaseQty = $item['purchase_qty'] ?? $item['board_qty'];
            if ($purchaseQty > $item['available_qty']) {
                $validationErrors[] = "{$item['description']}: Selected quantity ({$purchaseQty}) exceeds available quantity ({$item['available_qty']}).";
            }
            if ($purchaseQty < 1) {
                $validationErrors[] = "{$item['description']}: Selected quantity must be at least 1.";
            }
        }
        
        if (!empty($validationErrors)) {
            session()->flash('error', 'Validation failed: ' . implode(' ', $validationErrors));
            return;
        }
        
        $supplierId = $this->multipleSuppliersDetected ? $this->selectedSupplierId : $this->currentSelectedSupplierId;
        if (!$supplierId) {
            session()->flash('error', 'No supplier found for the selected items.');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePONumber(),
                'job_order_id' => $this->selectedJobOrderIds[0], // Use first selected job order
                'supplier_id' => $supplierId,
                'date' => $this->poDate, // Note: database field is 'date', not 'po_date'
                'status' => 'draft',
                'notes' => $this->notes,
            ]);
            
            Log::info('Purchase order created', [
                'purchase_order_id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'supplier_id' => $supplierId
            ]);
            
            // Create purchase order items and update job order quantities
            $itemsToProcess = $this->multipleSuppliersDetected ? $this->selectedSupplierItems : $this->selectedItems;
            
            foreach ($itemsToProcess as $item) {
                // Use purchase_qty for the actual purchase quantity
                $purchaseQty = $item['purchase_qty'] ?? $item['board_qty'];
                
                // Create purchase order item
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_type' => strtolower($item['type']),
                    'item_id' => $item['item_id'],
                    'description' => $item['description'],
                    'quantity' => $purchaseQty,
                    'unit_price' => $item['unit_cost'],
                    'total_price' => $purchaseQty * $item['unit_cost'],
                ]);
                
                // Update job order item quantity (deduct purchased quantity)
                // Note: We're not updating the order_qty directly, instead we track via PurchaseOrderItem
                // The remaining quantity is calculated by: order_qty - sum of PurchaseOrderItem quantities
                // This approach maintains data integrity and allows for better tracking
                
                Log::info('Purchase order item created', [
                    'item_type' => $item['type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $purchaseQty,
                    'purchase_order_id' => $purchaseOrder->id
                ]);
                
                // Log remaining quantity after purchase
                $itemModel = $item['type'] === 'BOX' ? JobOrderBox::find($item['item_id']) : JobOrderDivider::find($item['item_id']);
                if ($itemModel) {
                    $remainingQty = $this->calculateRemainingQuantity($itemModel);
                    Log::info('Remaining quantity after purchase', [
                        'item_id' => $item['item_id'],
                        'remaining_qty' => $remainingQty
                    ]);
                }
            }
            
            DB::commit();
            
            session()->flash('success', 'Purchase order created successfully!');
            return redirect()->route('purchase-order-management');
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    private function generatePONumber()
    {
        $lastPO = PurchaseOrder::orderBy('id', 'desc')->first();
        $nextNumber = $lastPO ? (int) str_replace('PO-', '', $lastPO->po_number) + 1 : 1;
        return 'PO-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function goBack()
    {
        if ($this->showFinalReview) {
            $this->showFinalReview = false;
            $this->showSupplierSelection = true;
        } elseif ($this->showSupplierSelection) {
            $this->showSupplierSelection = false;
            $this->showItemSelection = true;
        } elseif ($this->showItemSelection) {
            $this->showItemSelection = false;
            $this->showJobOrderSelection = true;
        }
    }

    public function render()
    {
        return view('livewire.create-purchase-order');
    }
}
