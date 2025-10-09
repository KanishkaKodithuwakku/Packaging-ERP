<tr class="hover:bg-gray-50 {{ $level == 0 ? 'bg-gray-50' : '' }}">
    <!-- Account Name with indentation -->
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
        <span style="padding-left: {{ $level * 24 }}px;">
            {{ $group['name'] }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
        Group
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
        {{ $group['cl_balance_dc'] }} {{ number_format(0, 2) }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
        @if (isset($group['cl_balance']))
            <span class="font-semibold text-{{ $group['cl_balance_dc'] === 'Dr' ? 'green' : 'red' }}-600">
                {{ $group['cl_balance_dc'] }} {{ number_format((float) $group['cl_balance'], 2) }}
            </span>
        @else
            0.00
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
        <a href="{{ route('edit-group', $group['id']) }}" wire:navigate
            class="text-indigo-600 hover:text-indigo-900 mr-3">
            Edit
        </a>
        <a wire:click.prevent="$emit('confirmDelete', {{ $group['id'] }})" href="#"
            class="text-red-600 hover:text-red-900"
            onclick="return confirm('Are you sure you want to delete this group?')">
            Delete
        </a>
    </td>
</tr>

{{-- Ledgers of this group --}}
@foreach ($group['ledgers'] as $ledger)
    <tr class="hover:bg-blue-50 bg-blue-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
            <a href="{{ route('reports.ledger-statement', ['ledger_id' => $ledger['id']]) }}"
                class="text-blue-600 hover:text-blue-800"
                style="padding-left: {{ ($level+1)*24 }}px;">
                [{{ $ledger['code'] }}] {{ $ledger['name'] }}
            </a>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">Ledger</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
           {{ $ledger['op_balance_dc'] }} {{ number_format($ledger['op_balance'], 2) }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
            <span class="font-semibold text-{{ $ledger['cl_balance_dc'] === 'D' ? 'green' : 'red' }}-600">
                {{ $ledger['cl_balance_dc'] }} {{ number_format($ledger['cl_balance'], 2) }}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
            <a href="{{ route('ledgers.edit', $ledger['id']) }}" wire:navigate
                class="text-indigo-600 hover:text-indigo-900 mr-3">
                Edit
            </a>
            <a wire:click.prevent="confirmDeleteLedger({{ $ledger['id'] }})" href="#"
                class="text-red-600 hover:text-red-900"
                onclick="return confirm('Are you sure you want to delete this ledger?')">
                Delete
            </a>
        </td>
    </tr>
@endforeach




{{-- Recursive call for child groups --}}
@foreach ($group['children'] as $child)
    @include('livewire.accounts.partials.group-row', ['group' => $child, 'level' => $level + 1])
@endforeach
