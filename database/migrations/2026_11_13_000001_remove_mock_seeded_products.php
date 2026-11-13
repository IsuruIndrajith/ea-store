<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $slugs = [
            'gold-titan-mt5',
            'night-owl-scalper',
            'aurum-ai-mt5',
            'trend-pulse-indicator',
            'steady-grid-pro',
            'falcon-breakout',
            'golden-hedge-source',
            'volatility-lens',
        ];

        $ids = DB::table('products')->whereIn('slug', $slugs)->pluck('id');

        // Remove child rows that block the delete
        if ($ids->isNotEmpty()) {
            DB::table('order_items')->whereIn('product_id', $ids)->delete();
        }

        DB::table('products')->whereIn('slug', $slugs)->delete();
    }

    public function down(): void
    {
        // Intentionally empty — mock products should not be restored.
    }
};
