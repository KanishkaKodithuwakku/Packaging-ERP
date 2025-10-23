<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Profit & Loss Report</h1>
                    <p class="text-gray-600">Trading and Profit & Loss statement showing income and expenses</p>
                </div>

                <!-- Report Options -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Report Options</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model.live="showOpeningBalance" 
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Show Opening Profit and Loss Statement</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Note: In opening P&L all ledgers balance must be zero.</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" 
                                   wire:model.live="startDate"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <p class="text-xs text-gray-500 mt-1">Leave empty for start of financial year</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" 
                                   wire:model.live="endDate"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <p class="text-xs text-gray-500 mt-1">Leave empty for end of financial year</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mb-6 flex gap-2">
                    <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        DOWNLOAD .CSV
                    </button>
                    <button class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        DOWNLOAD .XLS
                    </button>
                    <button class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        PRINT
                    </button>
                </div>

                <!-- Company Name and Subtitle -->
                <div class="text-center mb-6">
                    <div class="text-xl font-bold text-gray-900">{{ $companyName }}</div>
                    <div class="text-lg text-gray-700">{{ $subtitle }}</div>
                </div>

                <!-- Profit & Loss Statement -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-8">
                        <!-- Gross Expenses Section -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Gross Expenses (Dr)
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount (Rs)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {!! $this->renderAccountList($grossExpenses, 0) !!}
                                </tbody>
                            </table>
                        </div>

                        <!-- Gross Expense Totals -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <tbody class="bg-white">
                                    @if($grossExpenseTotal >= 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Total Gross Expenses</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">Dr {{ number_format($grossExpenseTotal, 2) }}</td>
                                        </tr>
                                    @else
                                        <tr class="bg-red-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-red-900 font-semibold">Total Gross Expenses</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-red-600">Dr {{ number_format($grossExpenseTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    @if($grossPL >= 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Gross Profit C/D</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">{{ number_format($grossPL, 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="bg-blue-50 font-bold">
                                        <td class="px-4 py-3 text-sm text-gray-900 font-bold">Total</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono font-bold">
                                            Dr {{ number_format($grossPL >= 0 ? $grossExpenseTotal + $grossPL : $grossExpenseTotal, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Net Expenses Section -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Net Expenses (Dr)
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount (Rs)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {!! $this->renderAccountList($netExpenses, 0) !!}
                                </tbody>
                            </table>
                        </div>

                        <!-- Net Expense Totals -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <tbody class="bg-white">
                                    @if($netExpenseTotal >= 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Total Expenses</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">Dr {{ number_format($netExpenseTotal, 2) }}</td>
                                        </tr>
                                    @else
                                        <tr class="bg-red-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-red-900 font-semibold">Total Expenses</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-red-600">Dr {{ number_format($netExpenseTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    @if($grossPL < 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Gross Loss B/D</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">{{ number_format(abs($grossPL), 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    @if($netPL >= 0)
                                        <tr class="bg-green-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-green-900 font-semibold">Net Profit</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-green-600">{{ number_format($netPL, 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="bg-blue-50 font-bold">
                                        <td class="px-4 py-3 text-sm text-gray-900 font-bold">Total</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono font-bold">
                                            Dr {{ number_format($netPL >= 0 ? $netExpenseTotal + $netPL : $netExpenseTotal + abs($grossPL), 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-8">
                        <!-- Gross Incomes Section -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Gross Incomes (Cr)
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount (Rs)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {!! $this->renderAccountList($grossIncomes, 0) !!}
                                </tbody>
                            </table>
                        </div>

                        <!-- Gross Income Totals -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <tbody class="bg-white">
                                    @if($grossIncomeTotal >= 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Total Gross Incomes</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">Cr {{ number_format($grossIncomeTotal, 2) }}</td>
                                        </tr>
                                    @else
                                        <tr class="bg-red-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-red-900 font-semibold">Total Gross Incomes</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-red-600">Cr {{ number_format($grossIncomeTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    @if($grossPL < 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Gross Loss C/D</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">{{ number_format(abs($grossPL), 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="bg-blue-50 font-bold">
                                        <td class="px-4 py-3 text-sm text-gray-900 font-bold">Total</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono font-bold">
                                            Cr {{ number_format($grossPL < 0 ? $grossIncomeTotal + abs($grossPL) : $grossIncomeTotal, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Net Incomes Section -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Net Incomes (Cr)
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount (Rs)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {!! $this->renderAccountList($netIncomes, 0) !!}
                                </tbody>
                            </table>
                        </div>

                        <!-- Net Income Totals -->
                        <div>
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <tbody class="bg-white">
                                    @if($netIncomeTotal >= 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Total Incomes</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">Cr {{ number_format($netIncomeTotal, 2) }}</td>
                                        </tr>
                                    @else
                                        <tr class="bg-red-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-red-900 font-semibold">Total Incomes</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-red-600">Cr {{ number_format($netIncomeTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    @if($grossPL >= 0)
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">Gross Profit B/D</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold">{{ number_format($grossPL, 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    @if($netPL < 0)
                                        <tr class="bg-red-50 font-semibold">
                                            <td class="px-4 py-3 text-sm text-red-900 font-semibold">Net Loss</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-red-600">{{ number_format(abs($netPL), 2) }}</td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="bg-blue-50 font-bold">
                                        <td class="px-4 py-3 text-sm text-gray-900 font-bold">Total</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono font-bold">
                                            Cr {{ number_format($netPL < 0 ? $netIncomeTotal + $grossPL - abs($netPL) : $netIncomeTotal + $grossPL, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Profit & Loss Summary</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium">Gross Profit/Loss:</span>
                                <span class="ml-2 font-mono {{ $grossPL >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $grossPL >= 0 ? '+' : '' }}{{ number_format($grossPL, 2) }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium">Net Profit/Loss:</span>
                                <span class="ml-2 font-mono {{ $netPL >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $netPL >= 0 ? '+' : '' }}{{ number_format($netPL, 2) }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium">Status:</span>
                                <span class="ml-2 font-semibold {{ $netPL >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $netPL >= 0 ? 'PROFITABLE' : 'LOSS' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
