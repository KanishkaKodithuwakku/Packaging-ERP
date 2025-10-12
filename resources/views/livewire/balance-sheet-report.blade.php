<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Balance Sheet Report</h1>
        <p class="text-gray-600">Financial position statement showing assets, liabilities and equity</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Options Panel -->
    <div class="mb-6">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Report Options</h3>
            </div>
            <div class="p-4">
                <form wire:submit.prevent="generateBalanceSheet">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Opening Balance Checkbox -->
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="showOpeningBalance"
                                wire:model.live="showOpeningBalance"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="showOpeningBalance" class="ml-2 text-sm text-gray-700">
                                Show Opening Balance Sheet
                            </label>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                                Start Date
                            </label>
                            <input 
                                type="date" 
                                id="startDate"
                                wire:model.live="startDate"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Leave empty for start of financial year
                            </p>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                                End Date
                            </label>
                            <input 
                                type="date" 
                                id="endDate"
                                wire:model.live="endDate"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Leave empty for end of financial year
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <button 
            wire:click="downloadCsv"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            DOWNLOAD .CSV
        </button>
        
        <button 
            wire:click="downloadXls"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            DOWNLOAD .XLS
        </button>
        
        <button 
            wire:click="printReport"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            PRINT
        </button>
    </div>

    <!-- Balance Sheet Title -->
    <div class="text-center mb-6">
        <div class="text-lg font-semibold text-gray-900">{{ $companyName }}</div>
        <div class="text-base text-gray-700">{{ $subtitle }}</div>
    </div>

    <!-- Balance Sheet Table -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assets Column -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assets (Dr)
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount (Rs)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(isset($balanceSheet['assets']))
                            {!! $this->renderAccountList($balanceSheet['assets'], 0) !!}
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Assets Totals -->
            <div class="border-t border-gray-200 bg-gray-50">
                <table class="min-w-full">
                    <tbody>
                        <!-- Total Assets -->
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                Total Assets
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-right font-mono">
                                {{ number_format($balanceSheet['assets_total'] ?? 0, 2) }}
                            </td>
                        </tr>
                        
                        <!-- Net Loss (if applicable) -->
                        @if(($balanceSheet['profit_loss'] ?? 0) < 0)
                            <tr class="border-b border-gray-200">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    Profit & Loss Account (Net Loss)
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-right font-mono">
                                    {{ number_format(abs($balanceSheet['profit_loss'] ?? 0), 2) }}
                                </td>
                            </tr>
                        @endif
                        
                        <!-- Final Total -->
                        <tr class="{{ isset($balanceSheet['is_balanced']) && $balanceSheet['is_balanced'] ? 'bg-green-50' : 'bg-red-50' }}">
                            <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                Total
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right font-mono">
                                {{ number_format($balanceSheet['final_assets_total'] ?? 0, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Liabilities Column -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Liabilities and Owners Equity (Cr)
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount (Rs)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(isset($balanceSheet['liabilities']))
                            {!! $this->renderAccountList($balanceSheet['liabilities'], 0) !!}
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Liabilities Totals -->
            <div class="border-t border-gray-200 bg-gray-50">
                <table class="min-w-full">
                    <tbody>
                        <!-- Total Liabilities -->
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                Total Liability and Owners Equity
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-right font-mono">
                                {{ number_format($balanceSheet['liabilities_total'] ?? 0, 2) }}
                            </td>
                        </tr>
                        
                        <!-- Net Profit (if applicable) -->
                        @if(($balanceSheet['profit_loss'] ?? 0) >= 0)
                            <tr class="border-b border-gray-200">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    Profit & Loss Account (Net Profit)
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-right font-mono">
                                    {{ number_format($balanceSheet['profit_loss'] ?? 0, 2) }}
                                </td>
                            </tr>
                        @endif
                        
                        <!-- Final Total -->
                        <tr class="{{ isset($balanceSheet['is_balanced']) && $balanceSheet['is_balanced'] ? 'bg-green-50' : 'bg-red-50' }}">
                            <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                Total
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right font-mono">
                                {{ number_format($balanceSheet['final_liabilities_total'] ?? 0, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Balance Check -->
    @if(isset($balanceSheet['is_balanced']) && !$balanceSheet['is_balanced'])
        <div class="mt-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>Warning:</strong> The balance sheet is not balanced. 
            Difference: Rs. {{ number_format(abs(($balanceSheet['final_assets_total'] ?? 0) - ($balanceSheet['final_liabilities_total'] ?? 0)), 2) }}
        </div>
    @elseif(isset($balanceSheet['is_balanced']) && $balanceSheet['is_balanced'])
        <div class="mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <strong>✓</strong> Balance sheet is balanced correctly.
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2">Generating Balance Sheet</h3>
                <p class="text-sm text-gray-500 mt-1">Please wait while we calculate the balances...</p>
            </div>
        </div>
    </div>
</div>
