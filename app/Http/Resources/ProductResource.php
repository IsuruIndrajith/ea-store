<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    private static function absUrl(?string $url): ?string
    {
        if (! $url) return null;
        $apiBase = rtrim(config('app.url'), '/');
        // Strip any wrong absolute origin so the correct API base is always used
        if (str_starts_with($url, 'http')) {
            $url = preg_replace('#^https?://[^/]+#', '', $url) ?? $url;
        }
        return $apiBase . '/' . ltrim($url, '/');
    }

    private static function fixExtendedImages(?array $extended): ?array
    {
        if (! $extended) return $extended;
        if (isset($extended['images']) && is_array($extended['images'])) {
            $extended['images'] = array_map(function ($img) {
                if (isset($img['url'])) {
                    $img['url'] = self::absUrl($img['url']);
                }
                return $img;
            }, $extended['images']);
        }
        return $extended;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'name'             => $this->name,
            'thumbnail_url'    => self::absUrl($this->thumbnail_url),
            'version'          => $this->version,
            'platform'         => $this->platform,
            'market'           => $this->market,
            'categories'       => $this->whenLoaded('categories', fn () => $this->categories->pluck('slug')->values()),
            'price'            => $this->price_cents / 100,
            'sale_price'       => $this->sale_price_cents / 100,
            'growth'           => (float) $this->growth,
            'drawdown'         => (float) $this->drawdown,
            'rating'           => (float) $this->rating,
            'reviews'          => $this->reviews,
            'hue'              => $this->hue,
            'description'       => $this->description,
            'short_description' => $this->short_description
                ?? $this->extended['short_description']
                ?? null,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'features'         => $this->features ?? [],
            'vendor_url'       => $this->vendor_url,
            'myfxbook_link'    => $this->myfxbook_link,
            'extended'         => self::fixExtendedImages($this->extended),
            // set_review_data: prefer the dedicated set_reviews table, fall back to legacy column
            'set_review_data'  => $this->whenLoaded('setReview', function () {
                $sr = $this->setReview;
                if (! $sr) {
                    return $this->set_review_data;
                }
                // Merge the top-level SEO fields into the content array so the
                // existing SetReviewTab component receives one flat object.
                $content = $sr->content ?? [];
                $content['recommended_slug'] = $sr->slug;
                $content['suggested_h1']     = $sr->h1;
                $content['focus_keyword']    = $sr->focus_keyword;
                $content['meta_description'] = $sr->meta_description;

                return $content;
            }, fn () => $this->set_review_data),
            'set_review_slug'  => $this->whenLoaded('setReview', fn () => $this->setReview?->is_published ? $this->setReview->slug : null),
            'is_purchasable'   => $this->is_active
                && $this->price_cents > 0
                && $this->sale_price_cents > 0
                && $this->sale_price_cents <= $this->price_cents
                && $this->file_path !== null
                && Storage::disk('local')->exists($this->file_path),
        ];
    }
}
