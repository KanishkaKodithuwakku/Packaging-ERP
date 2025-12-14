# Searchable Dropdown Implementation Guide

This guide provides step-by-step instructions for implementing a searchable dropdown component using Livewire, similar to the consumable items search box in the GRN detail page.

## Overview

The searchable dropdown component provides:
- Real-time search/filtering as you type
- Dropdown list that shows filtered results
- Auto-hide dropdown when item is selected
- Clear button (X icon) to reset selection
- Shows first N items by default when focused
- Click outside to close dropdown

## Backend Implementation (Livewire Component)

### 1. Add Component Properties

Add these properties to your Livewire component:

```php
// Search and selection properties
public $searchTerm = '';              // Current search text
public $selectedItemId = '';          // Selected item ID
public bool $showDropdown = false;    // Control dropdown visibility
public $itemValue = '';               // Additional value field (e.g., unit cost)
```

### 2. Add Methods for Dropdown Control

```php
/**
 * Show dropdown when input is focused
 */
public function showDropdown()
{
    $this->showDropdown = true;
}

/**
 * Clear item selection
 */
public function clearSelection()
{
    $this->selectedItemId = '';
    $this->searchTerm = '';
    $this->itemValue = '';
    $this->showDropdown = false;
}

/**
 * Handle search term changes and show dropdown
 */
public function updatedSearchTerm($value)
{
    // Show dropdown when typing
    if (!$this->showDropdown) {
        $this->showDropdown = true;
    }
    // Clear selection if search term is cleared
    if (empty($value)) {
        $this->selectedItemId = '';
        $this->itemValue = '';
    }
}

/**
 * Select item from dropdown
 */
public function selectItem($itemId)
{
    $this->selectedItemId = $itemId;
    $item = YourModel::find($itemId);
    if ($item) {
        // Auto-fill related fields
        $this->itemValue = $item->default_value; // Example: default_unit_cost
        $this->searchTerm = $item->code . ' - ' . $item->name;
        $this->showDropdown = false;
    }
}

/**
 * Get filtered items for dropdown
 */
public function getFilteredItems()
{
    $query = YourModel::where('is_active', true)
        ->orderBy('code');

    if (!empty($this->searchTerm)) {
        $searchTerm = '%' . $this->searchTerm . '%';
        $query->where(function($q) use ($searchTerm) {
            $q->where('code', 'like', $searchTerm)
              ->orWhere('name', 'like', $searchTerm);
        });
    }

    // If no search term, show first 5 items by default
    if (empty($this->searchTerm)) {
        return $query->limit(5)->get();
    }

    return $query->limit(10)->get();
}
```

### 3. Update Render Method

```php
public function render()
{
    $filteredItems = $this->getFilteredItems();

    return view('livewire.your-view', [
        'filteredItems' => $filteredItems,
    ]);
}
```

## Frontend Implementation (Blade View)

### 1. Basic Structure

```blade
<div class="mb-4 relative" id="searchable-dropdown-container">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Item * <!-- or your label -->
    </label>
    
    <!-- Hidden field for form submission -->
    <input type="hidden" wire:model="selectedItemId">
    
    <!-- Search Input -->
    <div class="relative">
        <input type="text" 
               wire:model.live="searchTerm" 
               wire:focus="showDropdown"
               id="item-search-input"
               class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Search by code or name..."
               onblur="setTimeout(() => @this.set('showDropdown', false), 200)">
        
        <!-- Dynamic Icon: Close (X) or Search -->
        @if($selectedItemId || $searchTerm)
            <button type="button" 
                    wire:click="clearSelection"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 hover:text-red-600 focus:outline-none">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @else
            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        @endif
    </div>
    
    <!-- Dropdown List -->
    @if($showDropdown)
        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
             onmousedown="event.preventDefault()"
             wire:ignore.self>
            @if(count($filteredItems) > 0)
                @foreach($filteredItems as $item)
                    <div wire:click="selectItem({{ $item->id }})" 
                         wire:key="item-{{ $item->id }}"
                         class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors {{ $selectedItemId == $item->id ? 'bg-blue-100' : '' }}">
                        <div class="font-medium text-gray-900">{{ $item->code }}</div>
                        <div class="text-sm text-gray-600">{{ $item->name }}</div>
                    </div>
                @endforeach
            @else
                <div class="px-4 py-2 text-gray-500 text-sm">No items found</div>
            @endif
        </div>
    @endif
    
    <!-- Error Message -->
    @error('selectedItemId') 
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
    @enderror
</div>
```

## Complete Example: Consumable Items Search

### Component (GRNDetail.php)

```php
<?php

namespace App\Livewire;

use App\Models\ConsumableItem;
use Livewire\Component;

class GRNDetail extends Component
{
    // Search properties
    public $itemSearchTerm = '';
    public $selectedConsumableItemId = '';
    public bool $showItemDropdown = false;
    public $itemUnitCost = '';

    /**
     * Show dropdown when input is focused
     */
    public function showItemDropdown()
    {
        $this->showItemDropdown = true;
    }

    /**
     * Clear item selection
     */
    public function clearItemSelection()
    {
        $this->selectedConsumableItemId = '';
        $this->itemSearchTerm = '';
        $this->itemUnitCost = '';
        $this->showItemDropdown = false;
    }

    /**
     * Handle search term changes
     */
    public function updatedItemSearchTerm($value)
    {
        if (!$this->showItemDropdown) {
            $this->showItemDropdown = true;
        }
        if (empty($value)) {
            $this->selectedConsumableItemId = '';
            $this->itemUnitCost = '';
        }
    }

    /**
     * Select consumable item from dropdown
     */
    public function selectConsumableItem($itemId)
    {
        $this->selectedConsumableItemId = $itemId;
        $consumable = ConsumableItem::find($itemId);
        if ($consumable) {
            $this->itemUnitCost = $consumable->default_unit_cost;
            $this->itemSearchTerm = $consumable->item_code . ' - ' . $consumable->item_name;
            $this->showItemDropdown = false;
        }
    }

    /**
     * Get filtered consumable items for dropdown
     */
    public function getFilteredConsumableItems()
    {
        $query = ConsumableItem::where('is_active', true)
            ->orderBy('item_code');

        if (!empty($this->itemSearchTerm)) {
            $searchTerm = '%' . $this->itemSearchTerm . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('item_code', 'like', $searchTerm)
                  ->orWhere('item_name', 'like', $searchTerm);
            });
        }

        if (empty($this->itemSearchTerm)) {
            return $query->limit(5)->get();
        }

        return $query->limit(10)->get();
    }

    public function render()
    {
        $filteredItems = $this->getFilteredConsumableItems();

        return view('livewire.grn-detail', [
            'filteredItems' => $filteredItems,
        ]);
    }
}
```

### View (grn-detail.blade.php)

```blade
<div class="mb-4 relative" id="consumable-item-container">
    <label class="block text-sm font-medium text-gray-700 mb-2">Consumable Item *</label>
    <input type="hidden" wire:model="selectedConsumableItemId">
    <div class="relative">
        <input type="text" 
               wire:model.live="itemSearchTerm" 
               wire:focus="showItemDropdown"
               id="consumable-item-search"
               class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Search by item code or name..."
               onblur="setTimeout(() => @this.set('showItemDropdown', false), 200)">
        @if($selectedConsumableItemId || $itemSearchTerm)
            <button type="button" 
                    wire:click="clearItemSelection"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 hover:text-red-600 focus:outline-none">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @else
            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        @endif
    </div>
    
    @if($showItemDropdown)
        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
             onmousedown="event.preventDefault()"
             wire:ignore.self>
            @if(count($filteredItems) > 0)
                @foreach($filteredItems as $item)
                    <div wire:click="selectConsumableItem({{ $item->id }})" 
                         wire:key="item-{{ $item->id }}"
                         class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors">
                        <div class="font-medium text-gray-900">{{ $item->item_code }}</div>
                        <div class="text-sm text-gray-600">{{ $item->item_name }}</div>
                    </div>
                @endforeach
            @else
                <div class="px-4 py-2 text-gray-500 text-sm">No items found</div>
            @endif
        </div>
    @endif
    
    @error('selectedConsumableItemId') 
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
    @enderror
</div>
```

## Key Features Explained

### 1. Real-time Search
- Uses `wire:model.live` for instant updates as you type
- Filters items by code and name using SQL LIKE queries

### 2. Dropdown Visibility Control
- Shows when input is focused (`wire:focus="showDropdown"`)
- Hides when item is selected (`$this->showDropdown = false`)
- Hides when clicking outside (using `onblur` with delay)

### 3. Dynamic Icon
- Shows search icon when field is empty
- Shows close (X) icon when there's text or selection
- Close button clears all related fields

### 4. Default Items Display
- Shows first 5 items when field is focused with no search term
- Shows up to 10 filtered items when searching

### 5. Click Prevention
- Uses `onmousedown="event.preventDefault()"` on dropdown to prevent blur event
- Uses `wire:ignore.self` to prevent Livewire from re-rendering dropdown on clicks

## Customization Options

### Change Default Item Count
```php
// In getFilteredItems() method
if (empty($this->searchTerm)) {
    return $query->limit(10)->get(); // Change 10 to desired number
}
```

### Change Max Results When Searching
```php
return $query->limit(20)->get(); // Change 20 to desired number
```

### Add More Search Fields
```php
$query->where(function($q) use ($searchTerm) {
    $q->where('code', 'like', $searchTerm)
      ->orWhere('name', 'like', $searchTerm)
      ->orWhere('description', 'like', $searchTerm); // Add more fields
});
```

### Change Dropdown Height
```blade
<!-- Change max-h-60 to max-h-40, max-h-80, etc. -->
<div class="... max-h-80 ...">
```

### Customize Styling
```blade
<!-- Change hover color -->
class="... hover:bg-green-50 ..."

<!-- Change selected item highlight -->
class="... {{ $selectedItemId == $item->id ? 'bg-green-100' : '' }}"
```

## Validation Example

```php
public function save()
{
    $this->validate([
        'selectedItemId' => 'required|exists:your_table,id',
        // other fields...
    ], [
        'selectedItemId.required' => 'Please select an item from the dropdown.',
        'selectedItemId.exists' => 'Selected item does not exist.',
    ]);

    // Your save logic here
}
```

## Troubleshooting

### Dropdown Not Closing After Selection
- Ensure `$this->showDropdown = false;` is set in `selectItem()` method
- Check that `wire:click` is properly bound

### Clear Button Not Working
- Use a dedicated method instead of inline `$set()` calls
- Ensure method is public and properly defined

### Search Not Filtering
- Check that `wire:model.live` is used (not just `wire:model`)
- Verify the search query logic in `getFilteredItems()`
- Check database column names match your query

### Dropdown Closing Too Fast
- Adjust the delay in `onblur` timeout (currently 200ms)
- Increase delay if needed: `setTimeout(() => @this.set('showDropdown', false), 300)`

## Best Practices

1. **Always use a hidden field** for the selected ID to ensure form submission works
2. **Use `wire:key`** on dropdown items for better Livewire performance
3. **Limit results** to prevent performance issues with large datasets
4. **Add loading states** if search takes time (use `wire:loading`)
5. **Handle empty states** gracefully with "No items found" message
6. **Use proper z-index** to ensure dropdown appears above other elements
7. **Test on mobile** devices as dropdown behavior may differ

## Advanced Features (Optional)

### Add Loading Indicator
```blade
<div wire:loading class="absolute right-3 top-1/2 transform -translate-y-1/2">
    <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
</div>
```

### Add Keyboard Navigation
```blade
<input type="text" 
       wire:keydown.arrow-down="..."
       wire:keydown.arrow-up="..."
       wire:keydown.enter="...">
```

### Add Debouncing for Better Performance
```php
public function updatedSearchTerm($value)
{
    // Add debounce logic if needed for heavy queries
    $this->dispatch('search-updated');
}
```

---

## Summary

This searchable dropdown component provides a user-friendly way to search and select items with:
- ✅ Real-time filtering
- ✅ Auto-hide on selection
- ✅ Clear button functionality
- ✅ Default items display
- ✅ Click outside to close
- ✅ Mobile-friendly design

Follow this guide to implement the same functionality anywhere in your application!

