<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = CustomerOrder::with(['items.menu'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('order_code', 'like', '%' . $request->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest('ordered_at')
            ->paginate(12)
            ->withQueryString();

        $statusSummary = CustomerOrder::query()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count")
            ->first();

        return view('orders.index', compact('orders', 'statusSummary'));
    }

    public function show(CustomerOrder $order)
    {
        $order->load(['items.menu.ingredients']);

        $ingredientSummary = collect();
        foreach ($order->items as $orderItem) {
            foreach ($orderItem->menu?->ingredients ?? collect() as $ingredient) {
                $qtyRequired = max((int) ($ingredient->pivot->qty_required ?? 1), 1) * $orderItem->qty;
                $existing = $ingredientSummary->get($ingredient->id, [
                    'name' => $ingredient->name,
                    'unit' => $ingredient->unit,
                    'qty_required' => 0,
                    'stock_now' => $ingredient->stock,
                    'min_stock' => $ingredient->min_stock,
                ]);
                $existing['qty_required'] += $qtyRequired;
                $ingredientSummary->put($ingredient->id, $existing);
            }
        }

        return view('orders.show', [
            'order' => $order,
            'ingredientSummary' => $ingredientSummary->values(),
        ]);
    }

    public function updateStatus(Request $request, CustomerOrder $order)
    {
        $request->validate([
            'status' => 'required|in:processing,completed,cancelled',
        ]);

        $nextStatus = $request->status;

        $result = DB::transaction(function () use ($order, $nextStatus) {
            /** @var CustomerOrder $lockedOrder */
            $lockedOrder = CustomerOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === CustomerOrder::STATUS_CANCELLED && $nextStatus !== CustomerOrder::STATUS_CANCELLED) {
                return ['ok' => false, 'message' => 'Pesanan yang sudah dibatalkan tidak bisa diproses lagi.'];
            }

            if ($lockedOrder->status === CustomerOrder::STATUS_COMPLETED && $nextStatus !== CustomerOrder::STATUS_COMPLETED) {
                return ['ok' => false, 'message' => 'Pesanan yang sudah selesai tidak bisa diubah lagi.'];
            }

            if ($nextStatus === CustomerOrder::STATUS_PROCESSING) {
                if ($lockedOrder->status === CustomerOrder::STATUS_PROCESSING) {
                    return ['ok' => false, 'message' => 'Pesanan ini sudah berada di status diproses.'];
                }

                $lockedOrder->update([
                    'status' => CustomerOrder::STATUS_PROCESSING,
                    'processing_at' => now(),
                ]);

                return ['ok' => true, 'message' => 'Pesanan dipindahkan ke status diproses.'];
            }

            if ($nextStatus === CustomerOrder::STATUS_CANCELLED) {
                $lockedOrder->update([
                    'status' => CustomerOrder::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);

                return ['ok' => true, 'message' => 'Pesanan berhasil dibatalkan.'];
            }

            if (! $lockedOrder->stock_applied_at) {
                $stockApply = $this->applyStockForCompletedOrder($lockedOrder);
                if (! $stockApply['ok']) {
                    return $stockApply;
                }
            }

            $lockedOrder->update([
                'status' => CustomerOrder::STATUS_COMPLETED,
                'processing_at' => $lockedOrder->processing_at ?: now(),
                'completed_at' => $lockedOrder->completed_at ?: now(),
                'stock_applied_at' => $lockedOrder->stock_applied_at ?: now(),
            ]);

            return ['ok' => true, 'message' => 'Pesanan selesai dan stok bahan sudah otomatis berkurang.'];
        });

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    protected function applyStockForCompletedOrder(CustomerOrder $order): array
    {
        $order->loadMissing(['items.menu.ingredients']);

        $requiredItems = [];

        foreach ($order->items as $orderItem) {
            if (! $orderItem->menu) {
                continue;
            }

            foreach ($orderItem->menu->ingredients as $ingredient) {
                $requiredQty = max((int) ($ingredient->pivot->qty_required ?? 1), 1) * $orderItem->qty;

                if (! isset($requiredItems[$ingredient->id])) {
                    $requiredItems[$ingredient->id] = [
                        'name' => $ingredient->name,
                        'qty' => 0,
                    ];
                }

                $requiredItems[$ingredient->id]['qty'] += $requiredQty;
            }
        }

        foreach ($requiredItems as $itemId => $data) {
            $item = Item::lockForUpdate()->findOrFail($itemId);
            if ($item->stock < $data['qty']) {
                return [
                    'ok' => false,
                    'message' => 'Pesanan belum bisa diselesaikan. Stok bahan ' . $item->name . ' kurang (' . $item->stock . ' tersisa, butuh ' . $data['qty'] . ').',
                ];
            }
        }

        foreach ($requiredItems as $itemId => $data) {
            $item = Item::lockForUpdate()->findOrFail($itemId);
            $before = $item->stock;
            $after = max($before - $data['qty'], 0);

            $item->update(['stock' => $after]);

            StockMovement::create([
                'item_id' => $item->id,
                'user_id' => auth()->id() ?: 1,
                'type' => 'out',
                'qty' => $data['qty'],
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => 'Pesanan selesai ' . $order->order_code,
                'movement_date' => now()->toDateString(),
            ]);
        }

        return ['ok' => true, 'message' => 'Stok berhasil diperbarui.'];
    }
}
