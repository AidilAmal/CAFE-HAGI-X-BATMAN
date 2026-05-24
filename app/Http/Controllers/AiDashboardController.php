<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiDashboardController extends Controller
{
    public function index(Request $request)
    {
        $defaultStock = (int) $request->integer('default_stock', 80);
        $forecastDays = (int) $request->integer('forecast_days', 30);

        $defaultStock = max(1, min($defaultStock, 100000));
        $forecastDays = max(1, min($forecastDays, 90));

        $fallback = $this->fallbackDashboard();
        $aiOnline = false;
        $errorMessage = null;

        try {
            $response = Http::connectTimeout(10)
                ->timeout($this->aiEngineTimeout())
                ->acceptJson()
                ->get($this->aiEngineUrl('/insights/dashboard'), [
                    'default_stock' => $defaultStock,
                    'forecast_days' => $forecastDays,
                ]);

            if ($response->successful()) {
                $dashboard = $response->json();
                $aiOnline = true;
            } else {
                $dashboard = $fallback;
                $errorMessage = 'AI Engine merespons error: HTTP ' . $response->status();

                Log::warning('AI dashboard request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $dashboard = $fallback;
            $errorMessage = 'Tidak bisa terhubung ke AI Engine. Pastikan AI Engine production sedang aktif.';

            Log::error('AI dashboard connection error', [
                'message' => $e->getMessage(),
            ]);
        }

        return view('ai.dashboard', [
            'dashboard' => $dashboard,
            'aiOnline' => $aiOnline,
            'errorMessage' => $errorMessage,
            'defaultStock' => $defaultStock,
            'forecastDays' => $forecastDays,
        ]);
    }

    private function aiEngineUrl(string $path = ''): string
    {
        $baseUrl = $_SERVER['AI_ENGINE_URL']
            ?? getenv('AI_ENGINE_URL')
            ?: config('services.ai_engine.url', 'http://127.0.0.1:8001');

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function aiEngineTimeout(): int
    {
        return (int) (
            $_SERVER['AI_ENGINE_TIMEOUT']
            ?? getenv('AI_ENGINE_TIMEOUT')
            ?: config('services.ai_engine.timeout', 30)
        );
    }

    private function fallbackDashboard(): array
    {
        return [
            'service' => 'Cafe Hagi AI Engine',
            'generated_at' => now()->toDateString(),
            'model' => [
                'ready' => false,
                'mae' => null,
                'rmse' => null,
                'r2' => null,
                'train_rows' => null,
                'test_rows' => null,
                'daily_menu_rows' => null,
            ],
            'kpis' => [
                'menus_analyzed' => 0,
                'critical_stock_count' => 0,
                'warning_stock_count' => 0,
                'promo_recommendation_count' => 0,
                'cluster_count' => 0,
            ],
            'stock_predictions' => [],
            'menu_clusters' => [
                'algorithm' => 'K-Means',
                'summary' => [],
                'items' => [],
                'note' => 'AI Engine offline.',
            ],
            'smart_promos' => [
                'method' => '-',
                'items' => [],
                'total_recommendations' => 0,
            ],
            'peak_hours' => [
                'top_hours' => [],
                'time_blocks' => [],
                'insights' => [],
            ],
            'menu_performance' => [],
        ];
    }
}
