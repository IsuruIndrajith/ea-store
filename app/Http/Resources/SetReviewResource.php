<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'product_slug'     => $this->product_slug,
            'h1'               => $this->h1,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'focus_keyword'    => $this->focus_keyword,
            'thumbnail_url'    => $this->thumbnail_url,
            'is_published'     => $this->is_published,
            'content'          => $this->content,
        ];
    }
}
