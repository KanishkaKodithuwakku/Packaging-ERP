<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\CustomerOrder;
use App\Models\JobOrder;
use App\Models\SupplierOrder;
use App\Models\Inventory;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'quotations' => Quotation::count(),
            'customer_orders' => CustomerOrder::count(),
            'job_orders' => JobOrder::count(),
            'supplier_orders' => SupplierOrder::count(),
            'inventory_items' => Inventory::count(),
        ];

        $recent_quotations = Quotation::with('customer')->whereHas('customer')->latest()->limit(5)->get();
        $recent_customer_orders = CustomerOrder::with('customer')->whereHas('customer')->latest()->limit(5)->get();
        $low_stock_items = Inventory::where('qty_available', '<', 10)->limit(5)->get();
        
        // Order status distribution for chart
        $order_status_counts = CustomerOrder::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('dashboard', compact(
            'stats',
            'recent_quotations',
            'recent_customer_orders',
            'low_stock_items',
            'order_status_counts'
        ));
    }
}
