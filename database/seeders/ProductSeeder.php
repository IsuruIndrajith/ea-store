<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::whereIn('slug', ['gold-scalper-mt5', 'trend-rider-mt4'])->update(['is_active' => false]);

        $categoryNames = [
            'forex-ea' => 'Forex EA',
            'gold-ea' => 'Gold EA',
            'low-drawdown' => 'Low Drawdown',
            'indicators' => 'Indicators',
            'source-code' => 'Source Code',
        ];

        $categories = collect($categoryNames)->mapWithKeys(fn ($name, $slug) => [
            $slug => Category::updateOrCreate(['slug' => $slug], ['name' => $name]),
        ]);

        $features = [
            'Fully automated — plug and play',
            'Includes optimized set files',
            'Hard stop loss on every trade',
            'Works with any broker, low spread recommended',
            'Free lifetime updates',
        ];

        $products = [
            ['gold-titan-mt5', 'Gold Titan EA', 'v5.2', 'MT5', 'XAUUSD', 84900, 4999, 298, 8.4, 4.8, 124, 80, ['gold-ea', 'forex-ea', 'low-drawdown']],
            ['night-owl-scalper', 'Night Owl Scalper', 'v4.1', 'MT4 & MT5', 'EURUSD', 29900, 4999, 186, 5.1, 4.6, 88, 250, ['forex-ea', 'low-drawdown']],
            ['aurum-ai-mt5', 'Aurum AI', 'v6.0', 'MT5', 'XAUUSD', 99900, 5999, 412, 12.2, 4.9, 203, 60, ['gold-ea', 'forex-ea']],
            ['trend-pulse-indicator', 'Trend Pulse Indicator', 'v2.3', 'MT4 & MT5', 'All pairs', 14900, 1999, 0, 0, 4.5, 51, 160, ['indicators']],
            ['steady-grid-pro', 'Steady Grid Pro', 'v3.8', 'MT4', 'GBPUSD', 39900, 3999, 154, 9.7, 4.4, 67, 20, ['forex-ea', 'source-code']],
            ['falcon-breakout', 'Falcon Breakout EA', 'v1.9', 'MT5', 'US30', 49900, 4499, 221, 6.3, 4.7, 95, 200, ['forex-ea', 'low-drawdown']],
            ['golden-hedge-source', 'Golden Hedge + Source', 'v2.0', 'MT4 & MT5', 'XAUUSD', 129900, 8999, 340, 14.5, 4.6, 42, 40, ['gold-ea', 'source-code']],
            ['volatility-lens', 'Volatility Lens', 'v1.4', 'MT5', 'All pairs', 9900, 1499, 0, 0, 4.3, 30, 300, ['indicators']],
        ];

        foreach ($products as [
            $slug, $name, $version, $platform, $market, $price, $salePrice,
            $growth, $drawdown, $rating, $reviews, $hue, $categorySlugs,
        ]) {
            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'version' => $version,
                    'platform' => $platform,
                    'market' => $market,
                    'description' => "{$name} is an automated Expert Advisor for {$platform} trading {$market}. It combines trend filters, adaptive lot sizing and strict risk controls.",
                    'price_cents' => $price,
                    'sale_price_cents' => $salePrice,
                    'growth' => $growth,
                    'drawdown' => $drawdown,
                    'rating' => $rating,
                    'reviews' => $reviews,
                    'hue' => $hue,
                    'features' => $features,
                    // TEST: 'gold-titan-mt5' points at the stub file for local checkout testing.
                    // All other products have no file and cannot be purchased until a real file is added.
                    'file_path' => ($slug === 'gold-titan-mt5') ? 'ea-files/ready.ex5' : null,
                    'is_active' => true,
                ]
            );

            $product->categories()->sync(collect($categorySlugs)->map(fn ($slug) => $categories[$slug]->id));
        }
    }
}
