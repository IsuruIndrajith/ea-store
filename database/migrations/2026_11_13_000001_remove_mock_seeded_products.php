<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->whereIn('slug', [
            'gold-titan-mt5',
            'night-owl-scalper',
            'aurum-ai-mt5',
            'trend-pulse-indicator',
            'steady-grid-pro',
            'falcon-breakout',
            'golden-hedge-source',
            'volatility-lens',
        ])->delete();
    }

    public function down(): void
    {
        // Intentionally empty — mock products should not be restored.
    }
};
