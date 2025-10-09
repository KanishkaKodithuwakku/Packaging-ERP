@props(['group', 'level' => 0])

@php
    $indent = $level * 20;
@endphp

<div class="border border-gray-200 rounded-lg mb-2" style="margin-left: {{ $indent }}px;">
    <div class="p-4 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-gray-900">{{ $group->name }}</h3>
                    @if($group->code)
                        <p class="text-xs text-gray-500">Code: {{ $group->code }}</p>
                    @endif
                    @if($group->currency)
                        <p class="text-xs text-gray-500">Currency: {{ $group->currency->code }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Group
                </span>
                @if($group->affects_gross)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Affects Gross
                    </span>
                @endif
                <div class="flex space-x-1">
                    <button wire:click="editGroup({{ $group->id }})" 
                            class="text-blue-600 hover:text-blue-900 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button wire:click="deleteGroup({{ $group->id }})" 
                            class="text-red-600 hover:text-red-900 text-sm"
                            onclick="return confirm('Are you sure you want to delete this group?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Child Groups -->
    @if($group->children->count() > 0)
        <div class="px-4 pb-2">
            @foreach($group->children as $childGroup)
                @include('livewire.partials.account-group-item', ['group' => $childGroup, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
    
    <!-- Ledgers in this group -->
    @if($group->ledgers->count() > 0)
        <div class="px-4 pb-4">
            <div class="space-y-2">
                @foreach($group->ledgers as $ledger)
                    <div class="border border-gray-200 rounded-lg p-3 bg-white" style="margin-left: {{ ($level + 1) * 20 }}px;">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $ledger->name }}</h4>
                                    @if($ledger->code)
                                        <p class="text-xs text-gray-500">Code: {{ $ledger->code }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        Balance: {{ number_format($ledger->op_balance, 2) }} {{ $ledger->op_balance_dc }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($ledger->type == 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Bank/Cash
                                    </span>
                                @endif
                                @if($ledger->reconciliation)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Reconciliation
                                    </span>
                                @endif
                                <div class="flex space-x-1">
                                    <button wire:click="editLedger({{ $ledger->id }})" 
                                            class="text-blue-600 hover:text-blue-900 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="deleteLedger({{ $ledger->id }})" 
                                            class="text-red-600 hover:text-red-900 text-sm"
                                            onclick="return confirm('Are you sure you want to delete this ledger?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
