<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $stats = Cache::remember('landing.stats', 300, function (): array {
            try {
                $tenantCount = Tenant::count();
                $orderCount  = $this->countOrdersAcrossAllSchemas();
            } catch (\Throwable) {
                $tenantCount = 0;
                $orderCount  = 0;
            }

            return [
                'tenants' => $tenantCount,
                'orders'  => $orderCount,
            ];
        });

        $plans = Cache::remember('landing.plans', 3600, function () {
            try {
                return Plan::where('is_active', true)
                    ->orderBy('price')
                    ->get()
                    ->toArray();
            } catch (\Throwable) {
                return [];
            }
        });

        // Wrap back into a collection so the view can use collection methods
        $plans = collect($plans)->map(fn (array $p) => (object) $p);

        return view('landing', compact('stats', 'plans'));
    }

    private function countOrdersAcrossAllSchemas(): int
    {
        $total = 0;

        foreach (Tenant::all() as $tenant) {
            try {
                DB::statement("SET search_path TO {$tenant->te_schema}, public");
                $total += DB::table('orders')->count();
            } catch (\Throwable) {
                // schema may not have orders table yet
            }
        }

        DB::statement('SET search_path TO public');

        return $total;
    }
}
