<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon_url' => $this->category->getFirstMediaUrl('icon') ?: $this->category->icon_url,
                'color_code' => $this->category->color_code,
            ],
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'icon_url' => $this->getFirstMediaUrl('icon') ?: $this->icon_url,
            'images' => $this->getMedia('images')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                ];
            }),
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'price_unit' => $this->price_unit,
            'duration' => $this->duration,
            'is_featured' => $this->is_featured,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
