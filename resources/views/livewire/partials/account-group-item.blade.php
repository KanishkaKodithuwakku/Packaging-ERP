@props(['group', 'level' => 0])

@php
    $indent = $level * 24;
    $groupBalance = $group->calculateClosingBalance();
    $groupOpeningBalance = $group->calculateOpeningBalance();
@endphp

<!-- Group Row -->
<tr class="hover:bg-gray-50">
    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: {{ 24 + $indent }}px;">
        <div class="flex items-center">
            <!-- Folder Icon -->
            <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
            </svg>
            <div>
                <div class="text-sm font-bold text-gray-900">{{ $group->name }}</div>
                @if($group->code)
                    <div class="text-xs text-gray-500">Code: {{ $group->code }}</div>
                @endif
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            Group
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
        {{ number_format($groupOpeningBalance, 2) }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
        @if($groupBalance['debit'] > 0 || $groupBalance['credit'] > 0)
            @if($groupBalance['debit'] > $groupBalance['credit'])
                Dr {{ number_format($groupBalance['debit'] - $groupBalance['credit'], 2) }}
            @else
                Cr {{ number_format($groupBalance['credit'] - $groupBalance['debit'], 2) }}
            @endif
        @else
            0.00
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
        <div class="flex items-center justify-center space-x-2">
            <button wire:click="editGroup({{ $group->id }})" 
                    class="text-gray-400 hover:text-blue-600 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <button wire:click="deleteGroup({{ $group->id }})" 
                    class="text-gray-400 hover:text-red-600 p-1"
                    onclick="return confirm('Are you sure you want to delete this group?')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </td>
</tr>

<!-- Child Groups -->
@if($group->children->count() > 0)
    @foreach($group->children as $childGroup)
        @include('livewire.partials.account-group-item', ['group' => $childGroup, 'level' => $level + 1])
    @endforeach
@endif

<!-- Ledgers in this group -->
@if($group->ledgers->count() > 0)
    @foreach($group->ledgers as $ledger)
        @php
            $ledgerBalance = $ledger->calculateClosingBalance();
            $ledgerOpeningBalance = $ledger->op_balance;
        @endphp
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap" style="padding-left: {{ 24 + ($level + 1) * 24 }}px;">
                <div class="flex items-center">
                    <!-- Document Icon -->
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $ledger->name }}</div>
                        @if($ledger->code)
                            <div class="text-xs text-gray-500">Code: {{ $ledger->code }}</div>
                        @endif
                        <div class="flex items-center space-x-2 mt-1">
                            @if($ledger->type == 1)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Bank/Cash
                                </span>
                            @endif
                            @if($ledger->reconciliation)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    Reconciliation
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Ledger
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                {{ number_format($ledgerOpeningBalance, 2) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                @if($ledgerBalance['debit'] > 0 || $ledgerBalance['credit'] > 0)
                    @if($ledgerBalance['debit'] > $ledgerBalance['credit'])
                        Dr {{ number_format($ledgerBalance['debit'] - $ledgerBalance['credit'], 2) }}
                    @else
                        Cr {{ number_format($ledgerBalance['credit'] - $ledgerBalance['debit'], 2) }}
                    @endif
                @else
                    0.00
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <div class="flex items-center justify-center space-x-2">
                    <button wire:click="editLedger({{ $ledger->id }})" 
                            class="text-gray-400 hover:text-blue-600 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button wire:click="deleteLedger({{ $ledger->id }})" 
                            class="text-gray-400 hover:text-red-600 p-1"
                            onclick="return confirm('Are you sure you want to delete this ledger?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
@endif