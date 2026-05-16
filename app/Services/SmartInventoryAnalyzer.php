<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SmartInventoryAnalyzer
{
    private const PREDICTION_DAYS = 7;
    private const EXPIRY_WARNING_DAYS = 7;

    public function analyze(): array
    {
        $stockPredictions = $this->stockPredictions();
        $expiredWarnings = $this->expiredWarnings();
        $promoRecommendations = $this->promoRecommendations($stockPredictions, $expiredWarnings);

        return [
            'summary' => $this->summary($stockPredictions, $expiredWarnings, $promoRecommendations),
            'stockPredictions' => $stockPredictions,
            'expiredWarnings' => $expiredWarnings,
            'promoRecommendations' => $promoRecommendations,
        ];
    }

    public function stockPredictions(): Collection
    {
        $usage = $this->usageTotals(self::PREDICTION_DAYS);
        $priority = [
            'out' => 1,
            'urgent' => 2,
            'warning' => 3,
            'low' => 4,
            'safe' => 5,
        ];

        return Item::with(['category', 'supplier'])
            ->where('status', 'active')
            ->get()
            ->map(function (Item $item) use ($usage) {
                $totalOut = (int) ($usage->get($item->id)->total_out ?? 0);
                $avgDailyUsage = round($totalOut / self::PREDICTION_DAYS, 2);
                $daysUntilEmpty = null;

                if ($item->stock <= 0) {
                    $daysUntilEmpty = 0;
                } elseif ($avgDailyUsage > 0) {
                    $daysUntilEmpty = (int) ceil($item->stock / $avgDailyUsage);
                }

                $status = $this->stockPredictionStatus($item, $daysUntilEmpty, $avgDailyUsage);
                $recommendedRestock = max(
                    (int) $item->min_stock * 2,
                    (int) ceil($avgDailyUsage * 14),
                    (int) $item->min_stock + 1
                );

                return [
                    'item' => $item,
                    'total_out' => $totalOut,
                    'avg_daily_usage' => $avgDailyUsage,
                    'days_until_empty' => $daysUntilEmpty,
                    'estimated_empty_date' => $daysUntilEmpty !== null ? Carbon::today()->addDays($daysUntilEmpty) : null,
                    'recommended_restock' => $recommendedRestock,
                    'status' => $status,
                    'status_label' => $this->stockPredictionLabel($status, $daysUntilEmpty),
                ];
            })
            ->filter(function (array $row) {
                return $row['status'] !== 'safe' || $row['avg_daily_usage'] > 0;
            })
            ->sortBy(function (array $row) use ($priority) {
                return sprintf('%02d-%05d', $priority[$row['status']] ?? 99, $row['days_until_empty'] ?? 9999);
            })
            ->take(8)
            ->values();
    }

    public function expiredWarnings(): Collection
    {
        return Item::with(['category', 'menus'])
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereNotNull('expired_at')
            ->whereDate('expired_at', '<=', Carbon::today()->addDays(self::EXPIRY_WARNING_DAYS))
            ->orderBy('expired_at')
            ->get()
            ->map(function (Item $item) {
                $expiredAt = $item->expired_at->copy()->startOfDay();
                $daysLeft = (int) Carbon::today()->diffInDays($expiredAt, false);

                return [
                    'item' => $item,
                    'days_left' => $daysLeft,
                    'expired_at' => $expiredAt,
                    'status' => $this->expiryStatus($daysLeft),
                    'status_label' => $this->expiryLabel($daysLeft),
                    'related_menus' => $item->menus->pluck('name')->take(3)->implode(', '),
                ];
            })
            ->values();
    }

    public function promoRecommendations(Collection $stockPredictions, Collection $expiredWarnings): Collection
    {
        $recommendations = collect();
        $addedMenuIds = collect();

        foreach ($expiredWarnings->take(5) as $warning) {
            /** @var Item $item */
            $item = $warning['item'];
            $menu = $item->menus->where('is_visible', true)->sortByDesc('price')->first();
            $discount = $this->discountByExpiry($warning['days_left']);

            if ($menu) {
                $addedMenuIds->push($menu->id);
            }

            $recommendations->push([
                'type' => 'expired',
                'title' => $menu ? 'Promo ' . $menu->name : 'Promo bahan ' . $item->name,
                'target' => $menu?->name ?? $item->name,
                'discount_percent' => $discount,
                'reason' => $item->name . ' ' . strtolower($warning['status_label']) . ' dan stok masih ' . $item->stock . ' ' . $item->unit . '.',
                'action' => $menu
                    ? 'Jalankan diskon ' . $discount . '% untuk mendorong penjualan sebelum bahan expired.'
                    : 'Buat paket/menu khusus memakai bahan ini sebelum expired.',
                'priority' => $warning['days_left'] <= 2 ? 'Tinggi' : 'Sedang',
            ]);
        }

        $usage14 = $this->usageTotals(14);
        $expiringItemIds = $expiredWarnings->pluck('item.id');

        $overstockItems = Item::with('menus')
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereNotIn('id', $expiringItemIds->all())
            ->get()
            ->filter(function (Item $item) use ($usage14) {
                $minStock = max((int) $item->min_stock, 1);
                $totalOut = (int) ($usage14->get($item->id)->total_out ?? 0);

                return $item->stock >= ($minStock * 3) && $totalOut <= max(3, $minStock);
            })
            ->sortByDesc('stock')
            ->take(3);

        foreach ($overstockItems as $item) {
            $menu = $item->menus
                ->where('is_visible', true)
                ->reject(fn ($menu) => $addedMenuIds->contains($menu->id))
                ->sortByDesc('price')
                ->first();

            if (! $menu) {
                continue;
            }

            $recommendations->push([
                'type' => 'overstock',
                'title' => 'Bundling ' . $menu->name,
                'target' => $menu->name,
                'discount_percent' => 10,
                'reason' => $item->name . ' stoknya tinggi (' . $item->stock . ' ' . $item->unit . ') tetapi pergerakan 14 hari terakhir rendah.',
                'action' => 'Buat promo bundling/diskon 10% untuk mempercepat perputaran stok.',
                'priority' => 'Normal',
            ]);
        }

        return $recommendations->take(6)->values();
    }

    private function usageTotals(int $days): Collection
    {
        $startDate = Carbon::today()->subDays($days - 1);

        return StockMovement::query()
            ->select('item_id', DB::raw('COALESCE(SUM(qty), 0) as total_out'))
            ->where('type', 'out')
            ->whereDate('movement_date', '>=', $startDate)
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');
    }

    private function stockPredictionStatus(Item $item, ?int $daysUntilEmpty, float $avgDailyUsage): string
    {
        if ($item->stock <= 0) {
            return 'out';
        }

        if ($daysUntilEmpty !== null && $daysUntilEmpty <= 3) {
            return 'urgent';
        }

        if ($daysUntilEmpty !== null && $daysUntilEmpty <= 7) {
            return 'warning';
        }

        if ($item->stock <= $item->min_stock) {
            return 'low';
        }

        return 'safe';
    }

    private function stockPredictionLabel(string $status, ?int $daysUntilEmpty): string
    {
        return match ($status) {
            'out' => 'Stok habis',
            'urgent' => 'Habis ' . $daysUntilEmpty . ' hari lagi',
            'warning' => 'Perlu restock minggu ini',
            'low' => 'Stok menipis',
            default => 'Aman',
        };
    }

    private function expiryStatus(int $daysLeft): string
    {
        if ($daysLeft < 0) {
            return 'expired';
        }

        if ($daysLeft <= 2) {
            return 'urgent';
        }

        return 'warning';
    }

    private function expiryLabel(int $daysLeft): string
    {
        if ($daysLeft < 0) {
            return 'Sudah expired ' . abs($daysLeft) . ' hari';
        }

        if ($daysLeft === 0) {
            return 'Expired hari ini';
        }

        return 'Expired ' . $daysLeft . ' hari lagi';
    }

    private function discountByExpiry(int $daysLeft): int
    {
        if ($daysLeft <= 0) {
            return 25;
        }

        if ($daysLeft <= 2) {
            return 20;
        }

        if ($daysLeft <= 5) {
            return 15;
        }

        return 10;
    }

    private function summary(Collection $stockPredictions, Collection $expiredWarnings, Collection $promoRecommendations): array
    {
        $stockRiskCount = $stockPredictions
            ->whereIn('status', ['out', 'urgent', 'warning', 'low'])
            ->count();

        $urgentStock = $stockPredictions
            ->whereIn('status', ['out', 'urgent'])
            ->count();

        $expiredRiskCount = $expiredWarnings->count();
        $promoCount = $promoRecommendations->count();

        $highlights = [];

        if ($urgentStock > 0) {
            $highlights[] = $urgentStock . ' barang diprediksi habis dalam 3 hari atau sudah habis.';
        } elseif ($stockRiskCount > 0) {
            $highlights[] = $stockRiskCount . ' barang perlu dimonitor untuk restock minggu ini.';
        } else {
            $highlights[] = 'Tidak ada barang kritis berdasarkan konsumsi 7 hari terakhir.';
        }

        if ($expiredRiskCount > 0) {
            $highlights[] = $expiredRiskCount . ' barang mendekati expired dan perlu diprioritaskan.';
        } else {
            $highlights[] = 'Tidak ada barang yang expired dalam 7 hari ke depan.';
        }

        if ($promoCount > 0) {
            $highlights[] = $promoCount . ' rekomendasi promo otomatis siap ditinjau admin.';
        } else {
            $highlights[] = 'Belum ada bahan yang cocok untuk promo otomatis hari ini.';
        }

        return [
            'stock_risk_count' => $stockRiskCount,
            'expired_risk_count' => $expiredRiskCount,
            'promo_count' => $promoCount,
            'period_label' => 'Analisis 7 hari terakhir',
            'highlights' => $highlights,
        ];
    }
}
