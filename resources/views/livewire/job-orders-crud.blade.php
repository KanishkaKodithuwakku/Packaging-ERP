<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Job Orders</h2>
                <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Job Order
                </button>
                
            </div>
        </div>

        @if(session()->has('message'))
            <div class="mx-6 mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="p-6">
            <!-- Job Orders Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Order No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Box Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($jobOrders as $jobOrder)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $jobOrder->jo_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $jobOrder->order_date ? $jobOrder->order_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $jobOrder->customerOrder?->customer?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $jobOrder->item_desc }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($jobOrder->box_length_cm && $jobOrder->box_width_cm && $jobOrder->box_height_cm)
                                        {{ $jobOrder->box_length_cm }} × {{ $jobOrder->box_width_cm }} × {{ $jobOrder->box_height_cm }} cm
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($jobOrder->order_qty ?? 0) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($jobOrder->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($jobOrder->status == 'in_progress') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $jobOrder->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $jobOrder->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    @if($jobOrder->status != 'completed')
                                        <a href="{{ route('material-requests') }}?create_from={{ $jobOrder->id }}" class="text-purple-600 hover:text-purple-900 mr-3">Create Material Request</a>
                                    @endif
                                    @if($jobOrder->status == 'in_progress')
                                        <button wire:click="complete({{ $jobOrder->id }})" class="text-green-600 hover:text-green-900 mr-3">Complete</button>
                                    @endif
                                    <button wire:click="delete({{ $jobOrder->id }})" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this job order?')">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $jobOrders->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-8 border w-full max-w-6xl shadow-lg rounded-md bg-white mb-10">
                <div class="mt-3">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">
                        {{ $editing ? 'Edit Job Order' : 'Create New Job Order' }}
                    </h3>
                    
                    <form wire:submit.prevent="save">
                        <!-- Header Section -->
                        <div class="mb-8 p-6 bg-blue-50 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Header Section</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Date
                                    </label>
                                    <input type="date" wire:model="order_date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('order_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Job No <span class="text-green-600">(Auto-generated)</span>
                                    </label>
                                    <input type="text" wire:model="jo_no" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-green-50 font-semibold focus:ring-blue-500 focus:border-blue-500" readonly>
                                    @error('jo_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Supplier PO (HJC)
                                    </label>
                                    <select wire:model="supplier_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Customer Name
                                    </label>
                                    <select wire:model="customer_order_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Customer Order</option>
                                @foreach($customerOrders as $customerOrder)
                                    <option value="{{ $customerOrder->id }}">
                                                {{ $customerOrder->customer->name }} - {{ $customerOrder->order_no }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_order_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Customer PO No
                                    </label>
                                    <input type="text" wire:model="customer_po_no" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('customer_po_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Supplier PO Reference
                                </label>
                                <input type="text" wire:model="supplier_po_ref" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('supplier_po_ref') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Order Details Section -->
                        <div class="mb-8 p-6 bg-yellow-50 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Order Details</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Item Description <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="item_desc" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                            @error('item_desc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                            <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Activity
                                    </label>
                                    <select wire:model="activity" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Activity</option>
                                        @foreach($activityOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('activity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Order Qty <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" step="0.01" wire:model="order_qty" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    @error('order_qty') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Selling Price
                                    </label>
                                    <input type="number" step="0.01" wire:model="selling_price" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('selling_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                            <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Stitched/Glued
                                    </label>
                                    <select wire:model="finishing_type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Type</option>
                                        @foreach($finishingOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('finishing_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantity to Make <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" step="0.01" wire:model="qty_to_make" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                            @error('qty_to_make') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                            </div>
                        </div>

                        <!-- Box Specification Section -->
                        <div class="mb-8 p-6 bg-green-50 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Box Specification</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Box Length (cm)
                                    </label>
                                    <input type="number" step="0.01" wire:model="box_length_cm" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('box_length_cm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Box Width (cm)
                                    </label>
                                    <input type="number" step="0.01" wire:model="box_width_cm" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('box_width_cm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Box Height (cm)
                                    </label>
                                    <input type="number" step="0.01" wire:model="box_height_cm" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('box_height_cm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Top Liner
                                    </label>
                                    <input type="text" wire:model="top_liner" placeholder="e.g. 135KL" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('top_liner') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Ply
                                    </label>
                                    <input type="number" wire:model="ply" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('ply') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Combination
                                    </label>
                                    <input type="text" wire:model="combination" placeholder="e.g. 112M / 140TL" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('combination') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Flute
                                    </label>
                                    <input type="text" wire:model="flute" placeholder="e.g. B, C, E" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('flute') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Sheet Width
                                    </label>
                                    <input type="number" step="0.01" wire:model="sheet_width" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('sheet_width') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Sheet Length
                                    </label>
                                    <input type="number" step="0.01" wire:model="sheet_length" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('sheet_length') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        No of UPS
                                    </label>
                                    <input type="number" wire:model="no_of_ups" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('no_of_ups') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Board Qty <span class="text-green-600">(Auto-calculated)</span>
                                    </label>
                                    <input type="number" step="0.01" wire:model="board_qty" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-green-50 font-semibold focus:ring-blue-500 focus:border-blue-500" readonly>
                                    @error('board_qty') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Size (mm)
                                    </label>
                                    <input type="number" wire:model="size_mm" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('size_mm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            @if($board_qty && $order_qty && $no_of_ups)
                                <div class="mt-4 p-3 bg-green-100 rounded-md">
                                    <p class="text-sm text-green-800">
                                        <strong>Formula:</strong> Board Qty = Order Qty ÷ No of UPS
                                        <br>
                                        <strong>Calculation:</strong> {{ number_format($order_qty, 2) }} ÷ {{ $no_of_ups }} = {{ number_format($board_qty, 2) }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Printing Details Section -->
                        <div class="mb-8 p-6 bg-purple-50 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Printing Details</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Printing Instruction
                                    </label>
                                    <select wire:model="printing_instruction" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Printing Type</option>
                                        @foreach($printingInstructionOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('printing_instruction') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        No of Colours
                                    </label>
                                    <input type="number" wire:model="no_of_colours" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('no_of_colours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Sample Available / Attached
                                    </label>
                                    <select wire:model="sample_available" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="Attached">Attached</option>
                                    </select>
                                    @error('sample_available') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        FSC Claim
                                    </label>
                                    <select wire:model="fsc_claim" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select FSC Claim</option>
                                        @foreach($fscClaimOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('fsc_claim') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Additional Notes</h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes
                                </label>
                                <textarea wire:model="notes" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 border-t pt-6">
                            <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                {{ $editing ? 'Update Job Order' : 'Create Job Order' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
