<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Packaging ERP Dashboard</h1>
                    
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                            <!-- Quick Stats Cards -->
                            <div class="bg-indigo-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-indigo-800">Quotations</h3>
                                <p class="text-3xl font-bold text-indigo-600">{{ $stats['quotations'] }}</p>
                            </div>
                            
                            <div class="bg-blue-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-blue-800">Customer Orders</h3>
                                <p class="text-3xl font-bold text-blue-600">{{ $stats['customer_orders'] }}</p>
                            </div>
                            
                            <div class="bg-green-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-green-800">Job Orders</h3>
                                <p class="text-3xl font-bold text-green-600">{{ $stats['job_orders'] }}</p>
                            </div>
                            
                            <div class="bg-yellow-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-yellow-800">Supplier Orders</h3>
                                <p class="text-3xl font-bold text-yellow-600">{{ $stats['supplier_orders'] }}</p>
                            </div>
                            
                            <div class="bg-purple-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-purple-800">Inventory Items</h3>
                                <p class="text-3xl font-bold text-purple-600">{{ $stats['inventory_items'] }}</p>
                            </div>
                        </div>

                    <!-- Charts and Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Order Status Pie Chart -->
                        <div class="bg-white border rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Order Status Distribution</h3>
                            <div class="relative h-64">
                                <canvas id="orderStatusChart"></canvas>
                            </div>
                        </div>

                            <!-- Recent Quotations -->
                            <div class="bg-white border rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4">Recent Quotations</h3>
                                <div class="space-y-3">
                                    @foreach($recent_quotations as $quotation)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <div>
                                                <p class="font-medium">{{ $quotation->qt_no }}</p>
                                                <p class="text-sm text-gray-600">{{ $quotation->customer ? $quotation->customer->name : 'No Customer' }}</p>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($quotation->status == 'draft') bg-gray-100 text-gray-800
                                                @elseif($quotation->status == 'sent') bg-blue-100 text-blue-800
                                                @elseif($quotation->status == 'accepted') bg-green-100 text-green-800
                                                @elseif($quotation->status == 'rejected') bg-red-100 text-red-800
                                                @elseif($quotation->status == 'expired') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($quotation->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Recent Customer Orders -->
                            <div class="bg-white border rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4">Recent Customer Orders</h3>
                                <div class="space-y-3">
                                    @foreach($recent_customer_orders as $order)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <div>
                                                <p class="font-medium">{{ $order->order_no }}</p>
                                                <p class="text-sm text-gray-600">{{ $order->customer ? $order->customer->name : 'No Customer' }}</p>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        <!-- Low Stock Items -->
                        <div class="bg-white border rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Low Stock Items</h3>
                            <div class="space-y-3">
                                @foreach($low_stock_items as $item)
                                    <div class="flex justify-between items-center p-3 bg-red-50 rounded">
                                        <div>
                                            <p class="font-medium">{{ $item->item_code }}</p>
                                            <p class="text-sm text-gray-600">{{ $item->lot_code }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                            {{ $item->qty_available }} {{ $item->uom }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Order Status Pie Chart
        const ctx = document.getElementById('orderStatusChart').getContext('2d');
        
            // Get order status data from backend
            const orderStatusData = {
                labels: {!! json_encode(array_keys($order_status_counts)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($order_status_counts)) !!},
                backgroundColor: [
                    '#3B82F6', // Blue for pending
                    '#10B981', // Green for confirmed
                    '#F59E0B', // Yellow for in_production
                    '#EF4444', // Red for completed
                    '#8B5CF6'  // Purple for delivered
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        };

        new Chart(ctx, {
            type: 'pie',
            data: orderStatusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
