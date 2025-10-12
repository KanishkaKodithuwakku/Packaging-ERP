<div>
    <div class="space-y-6">
        <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Finance Dashboard</h1>
            <p class="text-gray-600">Overview of your accounting system</p>
        </div>
        <div class="text-right">
            <div class="text-sm text-gray-500">Kings Packaging ERP</div>
            <div class="text-sm text-gray-700">{{ $accountDetails['financial_year_start'] }} to {{ $accountDetails['financial_year_end'] }}</div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column - Account Details, Bank Summary, Account Summary -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Account Details -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900">Account Details</h3>
                </div>
                <div class="p-6">
                    <table class="w-full">
                        <tbody class="space-y-2">
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700 w-1/4">Name</td>
                                <td class="py-2 text-gray-900">
                                    {{ $accountDetails['name'] }}<br>
                                    <span class="text-sm text-gray-600">{{ $accountDetails['address'] }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Email</td>
                                <td class="py-2 text-gray-900">{{ $accountDetails['email'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Role</td>
                                <td class="py-2 text-gray-900">{{ $accountDetails['role'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Currency</td>
                                <td class="py-2 text-gray-900">{{ $accountDetails['currency'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Financial Year</td>
                                <td class="py-2 text-gray-900">{{ $accountDetails['financial_year_start'] }} to {{ $accountDetails['financial_year_end'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Status</td>
                                <td class="py-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $accountDetails['status'] }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bank & Cash Summary -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900">Bank & Cash Summary</h3>
                </div>
                <div class="p-6">
                    @if(count($bankCashSummary) > 0)
                        <table class="w-full">
                            <tbody>
                                @foreach($bankCashSummary as $account)
                                    <tr>
                                        <td class="py-2 pr-4 text-gray-900">[{{ $account['code'] }}] {{ $account['name'] }}</td>
                                        <td class="py-2 text-right font-mono text-gray-900">{{ $account['formatted_balance'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500 text-center py-4">No bank or cash accounts found</p>
                    @endif
                </div>
            </div>

            <!-- Account Summary -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900">Account Summary</h3>
                </div>
                <div class="p-6">
                    <table class="w-full">
                        <tbody>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Assets</td>
                                <td class="py-2 text-right font-mono text-gray-900">{{ $this->formatCurrency($accountSummary['assets']['dc'], $accountSummary['assets']['amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Liabilities and Owners Equity</td>
                                <td class="py-2 text-right font-mono text-gray-900">{{ $this->formatCurrency($accountSummary['liabilities']['dc'], $accountSummary['liabilities']['amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Income</td>
                                <td class="py-2 text-right font-mono text-gray-900">{{ $this->formatCurrency($accountSummary['income']['dc'], $accountSummary['income']['amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium text-gray-700">Expense</td>
                                <td class="py-2 text-right font-mono text-gray-900">{{ $this->formatCurrency($accountSummary['expense']['dc'], $accountSummary['expense']['amount']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Recent Activity and Charts -->
        <div class="space-y-6">
            
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900">Recent Activity</h3>
                </div>
                <div class="p-6">
                    @if(count($recentActivity) > 0)
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($recentActivity as $activity)
                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900">{{ $activity['message'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $activity['date'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('accounting.entries') }}" class="text-sm text-blue-600 hover:text-blue-800">more</a>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No recent activity</p>
                    @endif
                </div>
            </div>

            <!-- Monthly Income & Expense Chart -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900">Monthly Income vs Expenses</h3>
                </div>
                <div class="p-6">
                    <canvas id="incomeExpenseChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Assets vs Liabilities Chart -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900">Assets vs Liabilities</h3>
                </div>
                <div class="p-6">
                    <canvas id="assetsLiabilitiesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Function to initialize the income vs expense chart
        function initializeIncomeExpenseChart() {
            console.log('Initializing Income vs Expense chart...');
            
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }
            
            // Get the canvas element
            const canvas = document.getElementById('incomeExpenseChart');
            if (!canvas) {
                console.error('Income vs Expense canvas element not found');
                return;
            }
            
            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }
            
            const ctx = canvas.getContext('2d');
            const monthlyIncomeData = @json($monthlyIncomeData ?? []);
            const monthlyExpenseData = @json($monthlyExpenseData ?? []);
            
            const chartData = {
                labels: monthlyIncomeData.map(item => item.month) || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Income',
                    data: monthlyIncomeData.map(item => item.amount) || [0, 0, 0, 0, 0, 0],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Expenses',
                    data: monthlyExpenseData.map(item => item.amount) || [0, 0, 0, 0, 0, 0],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.1
                }]
            };
            
            console.log('Income vs Expense chart data:', chartData);

            try {
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rs ' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                
                console.log('Income vs Expense chart created successfully:', chart);
            } catch (error) {
                console.error('Error creating Income vs Expense chart:', error);
                
                // Show fallback message
                const chartContainer = canvas.parentElement;
                chartContainer.innerHTML = '<div class="flex items-center justify-center h-48 text-gray-500"><p>Unable to load chart</p></div>';
            }
        }

        // Function to initialize the assets vs liabilities chart
        function initializeAssetsLiabilitiesChart() {
            console.log('Initializing Assets vs Liabilities chart...');
            
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }
            
            // Get the canvas element
            const canvas = document.getElementById('assetsLiabilitiesChart');
            if (!canvas) {
                console.error('Assets vs Liabilities canvas element not found');
                return;
            }
            
            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }
            
            const ctx = canvas.getContext('2d');
            const assetsData = @json($assetsLiabilitiesData ?? []);
            
            const chartData = {
                labels: ['Assets', 'Liabilities'],
                datasets: [{
                    data: [
                        assetsData.assets || 0, 
                        assetsData.liabilities || 0
                    ],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2
                }]
            };
            
            console.log('Assets vs Liabilities chart data:', chartData);

            try {
                const chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': Rs ' + context.parsed.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                
                console.log('Assets vs Liabilities chart created successfully:', chart);
            } catch (error) {
                console.error('Error creating Assets vs Liabilities chart:', error);
                
                // Show fallback message
                const chartContainer = canvas.parentElement;
                chartContainer.innerHTML = '<div class="flex items-center justify-center h-48 text-gray-500"><p>Unable to load chart</p></div>';
            }
        }

        // Function to initialize all charts
        function initializeAllCharts() {
            initializeIncomeExpenseChart();
            initializeAssetsLiabilitiesChart();
        }

        // Initialize charts on DOM ready
        document.addEventListener('DOMContentLoaded', initializeAllCharts);
        
        // Initialize charts when Livewire navigates (SPA navigation)
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated, re-initializing accounting dashboard charts...');
            // Small delay to ensure DOM is updated
            setTimeout(initializeAllCharts, 100);
        });
        
        // Also try to initialize after a short delay (fallback)
        setTimeout(function() {
            const canvas1 = document.getElementById('incomeExpenseChart');
            const canvas2 = document.getElementById('assetsLiabilitiesChart');
            
            if ((canvas1 && !Chart.getChart(canvas1)) || (canvas2 && !Chart.getChart(canvas2))) {
                console.log('Fallback: initializing accounting dashboard charts after delay...');
                initializeAllCharts();
            }
        }, 500);
    </script>
    </div>
</div>
