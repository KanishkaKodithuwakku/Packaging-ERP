<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Trial Balance</h1>
                    <p class="text-gray-600">Complete trial balance showing all accounts with opening balances, debit/credit totals, and closing balances</p>
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

                <!-- Trial Balance Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Account Name
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    O/P Balance (Rs)
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Debit Total (Rs)
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Credit Total (Rs)
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    C/L Balance (Rs)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {!! $this->renderAccountList($accountList, 0) !!}
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr class="{{ $isBalanced ? 'bg-green-50' : 'bg-red-50' }} font-bold">
                                <td class="px-4 py-4 text-sm font-bold text-gray-900">
                                    TOTAL
                                </td>
                                <td class="px-4 py-4 text-sm text-center"></td>
                                <td class="px-4 py-4 text-sm text-right"></td>
                                <td class="px-4 py-4 text-sm text-right font-mono font-bold">
                                    Dr {{ number_format($totalDr, 2) }}
                                </td>
                                <td class="px-4 py-4 text-sm text-right font-mono font-bold">
                                    Cr {{ number_format($totalCr, 2) }}
                                </td>
                                <td class="px-4 py-4 text-sm text-center">
                                    @if($isBalanced)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Balanced
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            Unbalanced
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary -->
                <div class="mt-8 p-4 {{ $isBalanced ? 'bg-green-50' : 'bg-red-50' }} rounded-lg">
                    <div class="text-center">
                        <h3 class="text-lg font-semibold {{ $isBalanced ? 'text-green-900' : 'text-red-900' }} mb-2">
                            Trial Balance Summary
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium">Total Debits:</span>
                                <span class="ml-2 font-mono font-semibold">Dr {{ number_format($totalDr, 2) }}</span>
                            </div>
                            <div>
                                <span class="font-medium">Total Credits:</span>
                                <span class="ml-2 font-mono font-semibold">Cr {{ number_format($totalCr, 2) }}</span>
                            </div>
                            <div>
                                <span class="font-medium">Difference:</span>
                                <span class="ml-2 font-mono font-semibold {{ $isBalanced ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $isBalanced ? '0.00' : number_format(abs($totalDr - $totalCr), 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="font-medium">Status:</span>
                            <span class="ml-2 font-bold {{ $isBalanced ? 'text-green-600' : 'text-red-600' }}">
                                {{ $isBalanced ? 'TRIAL BALANCE IS BALANCED ✓' : 'TRIAL BALANCE IS UNBALANCED ✗' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
