<div>
    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        <!-- Header with Back Button -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <button class="mr-4 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Chart of Accounts</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your account groups and ledgers</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button wire:click="openAddGroupModal" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Group
                </button>
                <button wire:click="openAddLedgerModal" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Ledger
                </button>
                <button wire:click="forceReset" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center"
                        title="Reset component if modals not working">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input wire:model.live="search" 
                           type="text" 
                           placeholder="Search groups and ledgers..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Account Structure Table -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">O/P Balance (Rs)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">C/L Balance (Rs)</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if($rootGroups->count() > 0)
                            @foreach($rootGroups as $group)
                                @include('livewire.partials.account-group-item', ['group' => $group, 'level' => 0])
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No account groups</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first account group.</p>
                                    <div class="mt-6">
                                        <button wire:click="openAddGroupModal" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Add Account Group
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Group Modal -->
    @if($showAddGroupModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="group-modal">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div id="group-modal-backdrop" 
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div id="group-modal-content" 
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit="addGroup">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add Account Group</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name *</label>
                                <input wire:model="groupForm.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('groupForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code</label>
                                <input wire:model="groupForm.code" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('groupForm.code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Parent Group</label>
                                <select wire:model="groupForm.parent_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">None (Root Group)</option>
                                    @foreach($allGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                @error('groupForm.parent_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Currency</label>
                                <select wire:model="groupForm.currency_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Default Currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->code }})</option>
                                    @endforeach
                                </select>
                                @error('groupForm.currency_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center">
                                <input wire:model="groupForm.affects_gross" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 block text-sm text-gray-900">Affects Gross Profit</label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea wire:model="groupForm.notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                                @error('groupForm.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Add Group
                        </button>
                        <button type="button" 
                                wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Ledger Modal -->
    @if($showAddLedgerModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="ledger-modal">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div id="ledger-modal-backdrop" 
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div id="ledger-modal-content" 
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form wire:submit="addLedger">
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                            {{ $isEditingLedger ? 'Edit Ledger' : 'Add Ledger' }}
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Ledger Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ledger name *</label>
                                <input wire:model="ledgerForm.name" type="text" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="Enter ledger name">
                                @error('ledgerForm.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Ledger Code -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ledger code (optional)</label>
                                <input wire:model="ledgerForm.code" type="text" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="Enter ledger code">
                                @error('ledgerForm.code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Parent Group -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Parent group *</label>
                                <select wire:model="ledgerForm.group_id" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="">Select a group</option>
                                    @foreach($allGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                @error('ledgerForm.group_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Opening Balance Section -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Opening Balance *</label>
                                <div class="flex items-center space-x-3">
                                    <div class="flex-1">
                                        <select wire:model="ledgerForm.op_balance_dc" 
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                            <option value="D">Dr</option>
                                            <option value="C">Cr</option>
                                        </select>
                                    </div>
                                    <div class="flex-1">
                                        <input wire:model="ledgerForm.op_balance" type="number" step="0.01" 
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div class="mt-2 p-3 bg-blue-50 rounded-md">
                                    <p class="text-xs text-blue-800">
                                        <strong>Note:</strong> Assets / Expenses always have Dr balance and Liabilities / Incomes always have Cr balance.
                                    </p>
                                </div>
                                @error('ledgerForm.op_balance') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                @error('ledgerForm.op_balance_dc') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Bank or Cash Account -->
                            <div>
                                <div class="flex items-center">
                                    <input wire:model="ledgerForm.type" 
                                           type="checkbox" 
                                           value="1"
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                           @if($ledgerForm['type'] == 1) checked @endif>
                                    <label class="ml-2 block text-sm text-gray-900">Bank or cash account</label>
                                </div>
                                <div class="mt-2 p-3 bg-blue-50 rounded-md">
                                    <p class="text-xs text-blue-800">
                                        <strong>Note:</strong> Select if the ledger account is a bank or a cash account.
                                    </p>
                                </div>
                            </div>

                            <!-- Reconciliation -->
                            <div>
                                <div class="flex items-center">
                                    <input wire:model="ledgerForm.reconciliation" 
                                           type="checkbox" 
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                           @if($ledgerForm['reconciliation']) checked @endif>
                                    <label class="ml-2 block text-sm text-gray-900">Reconciliation</label>
                                </div>
                                <div class="mt-2 p-3 bg-blue-50 rounded-md">
                                    <p class="text-xs text-blue-800">
                                        <strong>Note:</strong> If selected the ledger account can be reconciled from Reports > Reconciliation.
                                    </p>
                                </div>
                            </div>

                            <!-- Currency -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select wire:model="ledgerForm.currency_id" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="">Default Currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->code }})</option>
                                    @endforeach
                                </select>
                                @error('ledgerForm.currency_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea wire:model="ledgerForm.notes" 
                                          rows="4" 
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                          placeholder="Enter any additional notes..."></textarea>
                                @error('ledgerForm.notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $isEditingLedger ? 'Update Ledger' : 'Submit' }}
                        </button>
                        <button type="button" 
                                wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
