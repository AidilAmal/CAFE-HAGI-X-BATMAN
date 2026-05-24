<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiPredictionController extends Controller
{
    public function stockOut(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'current_stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'forecast_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        $menu->loadMissing('category');

        $payload = [
            'menu_id' => (int) $menu->id,
            'menu_name' => $menu->name,
            'menu_category' => $this->resolveMenuCategory($menu),
            'is_coffee_based' => $this->isCoffeeBased($menu),
            'menu_segment' => $this->resolveMenuSegment($menu->name),
            'unit_price' => (int) round((float) $menu->price),
            'current_stock' => (int) $validated['current_stock'],
            'start_date' => now()->toDateString(),
            'forecast_days' => (int) ($validated['forecast_days'] ?? 30),
        ];

        try {
            $response = Http::connectTimeout(3)
                ->timeout(config('services.ai_engine.timeout', 10))
                ->acceptJson()
                ->asJson()
                ->post(rtrim(config('services.ai_engine.url', 'http://127.0.0.1:8001'), '/') . '/predict/stock-out', $payload);

            if (! $response->successful()) {
                Log::warning('AI stock-out prediction failed', [
                    'status' => $response->status(),
                    'payload' => $payload,
                    'body' => $response->body(),
                ]);

                return back()->with('error', 'AI Engine gagal memproses prediksi stok. Cek terminal FastAPI dan log Laravel.');
            }

            return back()->with('ai_stock_prediction', $response->json());
        } catch (\Throwable $e) {
            Log::error('AI Engine connection error', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return back()->with('error', 'Tidak bisa terhubung ke AI Engine. Pastikan FastAPI berjalan di port 8001.');
        }
    }

    private function resolveMenuCategory(Menu $menu): string
    {
        $text = strtolower($menu->name . ' ' . ($menu->category?->name ?? ''));

        if ($this->containsAny($text, ['kopi', 'coffee', 'americano', 'cappuccino', 'macchiato', 'espresso', 'brew'])) {
            return 'Coffee';
        }

        if ($this->containsAny($text, ['matcha', 'tea', 'lemon', 'non coffee', 'non-coffee'])) {
            return 'Non Coffee';
        }

        if ($this->containsAny($text, ['croissant', 'pastry', 'bread', 'cake'])) {
            return 'Pastry';
        }

        if ($this->containsAny($text, ['burger', 'rice', 'bowl', 'food', 'makanan', 'chicken', 'beef'])) {
            return 'Food';
        }

        return $menu->category?->name ?: 'Unknown';
    }

    private function isCoffeeBased(Menu $menu): bool
    {
        $text = strtolower($menu->name . ' ' . ($menu->category?->name ?? ''));

        return $this->containsAny($text, [
            'kopi',
            'coffee',
            'americano',
            'cappuccino',
            'macchiato',
            'espresso',
            'latte',
            'brew',
        ]);
    }

    private function resolveMenuSegment(string $menuName): string
    {
        $normalized = strtolower(trim($menuName));

        $bestsellers = [
            'es kopi susu',
            'americano',
            'matcha latte',
        ];

        $deadStocks = [
            'manual brew v60',
            'lemon tea',
        ];

        if (in_array($normalized, $bestsellers, true)) {
            return 'bestseller';
        }

        if (in_array($normalized, $deadStocks, true)) {
            return 'dead_stock';
        }

        return 'normal';
    }

    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
