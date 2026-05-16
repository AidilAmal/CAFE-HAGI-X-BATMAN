<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\Item;
use App\Models\Menu;
use App\Models\StockMovement;
use App\Services\SmartInventoryAnalyzer;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(SmartInventoryAnalyzer $smartAnalyzer)
    {
        $orderSummary = CustomerOrder::query()
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders")
            ->first();

        $totalItems = Item::count();
        $totalMenus = Menu::count();
        $totalOrders = (int) ($orderSummary->total_orders ?? 0);
        $pendingOrders = (int) ($orderSummary->pending_orders ?? 0);
        $processingOrders = (int) ($orderSummary->processing_orders ?? 0);
        $completedOrders = (int) ($orderSummary->completed_orders ?? 0);
        $cancelledOrders = (int) ($orderSummary->cancelled_orders ?? 0);
        $lowStockItems = Item::whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count();
        $outOfStockItems = Item::where('stock', '<=', 0)->count();
        $recentMovements = StockMovement::with('item', 'user')->latest()->take(8)->get();
        $restockItems = Item::whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->take(6)->get();
        $recentOrders = CustomerOrder::with('items.menu')->latest('ordered_at')->take(6)->get();
        $smartInventory = $smartAnalyzer->analyze();
        $smartSummary = $smartInventory['summary'];
        $stockPredictions = $smartInventory['stockPredictions'];
        $expiredWarnings = $smartInventory['expiredWarnings'];
        $promoRecommendations = $smartInventory['promoRecommendations'];

        $topMenus = Menu::query()
            ->select('menus.*')
            ->selectSub(function ($query) {
                $query->from('customer_order_items')
                    ->join('customer_orders', 'customer_order_items.customer_order_id', '=', 'customer_orders.id')
                    ->whereColumn('customer_order_items.menu_id', 'menus.id')
                    ->where('customer_orders.status', 'completed')
                    ->selectRaw('COALESCE(SUM(customer_order_items.qty), 0)');
            }, 'sold_qty')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get();

        $startDate = Carbon::today()->subDays(6);
        $labels = [];
        $inSeries = [];
        $outSeries = [];

        for ($date = $startDate->copy(); $date->lte(Carbon::today()); $date->addDay()) {
            $labels[] = $date->format('d M');
            $inSeries[] = (int) StockMovement::whereDate('movement_date', $date)->where('type', 'in')->sum('qty');
            $outSeries[] = (int) StockMovement::whereDate('movement_date', $date)->where('type', 'out')->sum('qty');
        }

        return view('dashboard.index', compact(
            'totalItems',
            'totalMenus',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'cancelledOrders',
            'lowStockItems',
            'outOfStockItems',
            'recentMovements',
            'restockItems',
            'labels',
            'inSeries',
            'outSeries',
            'topMenus',
            'recentOrders',
            'smartSummary',
            'stockPredictions',
            'expiredWarnings',
            'promoRecommendations'
        ));
    }
}
