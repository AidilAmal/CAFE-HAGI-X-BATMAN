<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ?string $startDate = null,
        protected ?string $endDate = null,
        protected ?string $type = null
    ) {}

    public function collection()
    {
        $query = StockMovement::with('item', 'user')->latest();

        if ($this->startDate) {
            $query->whereDate('movement_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('movement_date', '<=', $this->endDate);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        return $query->get()->map(function ($movement) {
            return [
                'tanggal' => optional($movement->movement_date)->format('Y-m-d'),
                'barang' => $movement->item?->name,
                'tipe' => strtoupper($movement->type),
                'qty' => $movement->qty,
                'stok_sebelum' => $movement->stock_before,
                'stok_sesudah' => $movement->stock_after,
                'user' => $movement->user?->name,
                'note' => $movement->note,
            ];
        });
    }

    public function headings(): array
    {
        return ['Tanggal', 'Barang', 'Tipe', 'Qty', 'Stok Sebelum', 'Stok Sesudah', 'User', 'Note'];
    }
}
