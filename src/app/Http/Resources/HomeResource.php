<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'source' => [
                'id' => $this->source->id,
                'name' => $this->source->name,
            ],
            'thumbnailUrl' => $this->thumbnail_url,
            'categories' => $this->categories->map(fn($c) => [
                'name' => $c->name,
            ]),
            'likeCount' => $this->source_like_count,
            'bookmarked' => $this->bookmarks->isNotEmpty(),
            'publishedAt' => $this->published_at->toISOString(),
        ];
    }
}
