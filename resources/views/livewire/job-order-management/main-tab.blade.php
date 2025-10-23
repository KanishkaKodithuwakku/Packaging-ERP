<div class="space-y-6">
    <!-- Header Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
            <input type="date" 
                   wire:model="form.date" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-blue-50">
            @error('form.date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                Auto-generated
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
            <select wire:model.live="form.supplier_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-yellow-50">
                <option value="">Please select...</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->code }})</option>
                @endforeach
            </select>
            @error('form.supplier_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier PO Number</label>
            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-green-50 text-gray-900">
                {{ $form['supplier_po_number'] ?: 'Will be generated after supplier selection' }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
            <select wire:model.live="form.customer_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-yellow-50">
                <option value="">Please select...</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                @endforeach
            </select>
            @error('form.customer_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-red-50 text-gray-900 min-h-[40px]">
                {{ $form['customer_address'] ?: 'Will be auto-filled from customer selection' }}
            </div>
            @error('form.customer_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order No</label>
            <input type="text" 
                   wire:model="form.purchase_order_no" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-blue-50"
                   placeholder="Customer PO number">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PO Date</label>
            <input type="date" 
                   wire:model="form.po_date" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-blue-50">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea wire:model="form.notes" 
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-blue-50"
                  placeholder="Additional notes..."></textarea>
    </div>

    <!-- Status -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select wire:model="form.status" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-yellow-50">
            <option value="draft">Draft</option>
            <option value="confirmed">Confirmed</option>
            <option value="in_production">In Production</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <!-- Color Legend -->
    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Field Color Legend:</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded mr-2"></div>
                <span>Blue: User Input</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded mr-2"></div>
                <span>Yellow: Dropdown</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-100 border border-green-300 rounded mr-2"></div>
                <span>Green: Auto-calculated</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-100 border border-red-300 rounded mr-2"></div>
                <span>Red: Auto-filled</span>
            </div>
        </div>
    </div>
</div>
