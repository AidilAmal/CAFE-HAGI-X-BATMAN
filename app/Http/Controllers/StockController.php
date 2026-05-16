<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function inForm()
    {
        $items = Item::orderBy('name')->get();
        return view('stock.in', compact('items'));
    }

    public function storeIn(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer|min:1',
            'movement_date' => 'required|date',
            'expired_at' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $item = Item::lockForUpdate()->findOrFail($request->item_id);
            $before = $item->stock;
            $after = $before + $request->qty;

            $updateData = ['stock' => $after];
            if ($request->filled('expired_at')) {
                $updateData['expired_at'] = $request->expired_at;
            }

            $item->update($updateData);

            StockMovement::create([
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'qty' => $request->qty,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $request->note,
                'movement_date' => $request->movement_date,
            ]);
        });

        return redirect()->route('stock.history')->with('success', 'Stok masuk berhasil disimpan.');
    }

    public function outForm()
    {
        $items = Item::orderBy('name')->get();
        return view('stock.out', compact('items'));
    }

    public function storeOut(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer|min:1',
            'movement_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $item = Item::lockForUpdate()->findOrFail($request->item_id);

                if ($item->stock < $request->qty) {
                    throw new \RuntimeException('Stok tidak mencukupi.');
                }

                $before = $item->stock;
                $after = $before - $request->qty;

                $item->update(['stock' => $after]);

                StockMovement::create([
                    'item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'qty' => $request->qty,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'note' => $request->note,
                    'movement_date' => $request->movement_date,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['qty' => $e->getMessage()])->withInput();
        }

        return redirect()->route('stock.history')->with('success', 'Stok keluar berhasil disimpan.');
    }

    public function adjustmentForm()
    {
        $items = Item::orderBy('name')->get();
        return view('stock.adjustment', compact('items'));
    }

    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'final_stock' => 'required|integer|min:0',
            'movement_date' => 'required|date',
            'note' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $item = Item::lockForUpdate()->findOrFail($request->item_id);
            $before = $item->stock;
            $after = (int) $request->final_stock;
            $qty = abs($after - $before);

            $item->update(['stock' => $after]);

            StockMovement::create([
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'qty' => $qty,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $request->note,
                'movement_date' => $request->movement_date,
            ]);
        });

        return redirect()->route('stock.history')->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    public function history(Request $request)
    {
        $query = StockMovement::with('item', 'user')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $movements = $query->paginate(12)->withQueryString();

        return view('stock.history', compact('movements'));
    }
}
