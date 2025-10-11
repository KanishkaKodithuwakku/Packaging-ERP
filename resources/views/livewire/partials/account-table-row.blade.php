@props(['group', 'level' => 0])

@php
    $indent = $level * 20;
    $groupBalance = $group->calculateGroupBalance();
    $isGroup = true;
@endphp

<!-- Group Row -->
<tr class="bg-gray-50">
    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: {{ 24 + $indent }}px;">
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
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
    <td class="px-6 py-4 whitespace-nowrap text-right">
        <div class="text-sm font-bold text-gray-900">0.00</div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right">
        <div class="text-sm font-bold text-gray-900">
            @if($groupBalance['amount'] > 0)
                {{ $groupBalance['dc'] == 'D' ? 'Dr' : 'Cr' }} {{ number_format($groupBalance['amount'], 2) }}
            @else
                0.00
            @endif
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center">
        <div class="flex justify-center space-x-2">
            <button wire:click="editGroup({{ $group->id }})" 
                    class="text-blue-600 hover:text-blue-900" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <button wire:click="deleteGroup({{ $group->id }})" 
                    class="text-red-600 hover:text-red-900" title="Delete"
                    onclick="return confirm('Are you sure you want to delete this group?')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </td>
</tr>

<!-- Ledgers in this group -->
@if($group->ledgers->count() > 0)
    @foreach($group->ledgers as $ledger)
        @php
            $ledgerBalance = $ledger->closingBalance();
        @endphp
        <tr class="bg-white hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap" style="padding-left: {{ 44 + $indent }}px;">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <div class="text-sm text-gray-900">
                            @if($ledger->code)
                                [{{ $ledger->code }}] {{ $ledger->name }}
                            @else
                                {{ $ledger->name }}
                            @endif
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Ledger
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="text-sm text-gray-900">
                    {{ number_format($ledger->op_balance ?? 0, 2) }}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="text-sm text-gray-900">
                    @if($ledgerBalance['amount'] > 0)
                        {{ $ledgerBalance['dc'] == 'D' ? 'Dr' : 'Cr' }} {{ number_format($ledgerBalance['amount'], 2) }}
                    @else
                        0.00
                    @endif
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex justify-center space-x-2">
                    <button wire:click="editLedger({{ $ledger->id }})" 
                            class="text-blue-600 hover:text-blue-900" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button wire:click="deleteLedger({{ $ledger->id }})" 
                            class="text-red-600 hover:text-red-900" title="Delete"
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

<!-- Child Groups -->
@if($group->children->count() > 0)
    @foreach($group->children as $childGroup)
        @include('livewire.partials.account-table-row', ['group' => $childGroup, 'level' => $level + 1])
    @endforeach
@endif

