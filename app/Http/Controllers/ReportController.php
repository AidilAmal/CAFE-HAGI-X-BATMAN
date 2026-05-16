<?php

namespace App\Http\Controllers;

use App\Exports\StockReportExport;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected function filteredQuery(Request $request)
    {
        $query = StockMovement::with('item', 'user')->latest();

        if ($request->filled('start_date')) {
            $query->whereDate('movement_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('movement_date', '<=', $request->end_date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $movements = $this->filteredQuery($request)->paginate(15)->withQueryString();
        return view('reports.index', compact('movements'));
    }

    public function exportPdf(Request $request)
    {
        $movements = $this->filteredQuery($request)->get();
        $pdf = Pdf::loadView('reports.pdf', [
            'movements' => $movements,
            'filters' => $request->only('start_date', 'end_date', 'type'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-stok-cafe-hagi.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new StockReportExport($request->start_date, $request->end_date, $request->type),
            'laporan-stok-cafe-hagi.xlsx'
        );
    }
}
