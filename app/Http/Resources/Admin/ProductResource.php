<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BaseResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use JsonSerializable;

class ProductResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => [
                'en' => $this->getTranslation('title', 'en'),
                'ar' => $this->getTranslation('title', 'ar'),
            ],
            'is_active' => $this->active ? 'Published' : 'Pending',
            'active' => (bool)$this->active,
            'description' => [
                'en' => $this->getTranslation('description', 'en'),
                'ar' => $this->getTranslation('description', 'ar'),
            ],
            'media' => $this->media($this),
        ];
    }

    public function media($obj)
    {
        $media = $obj->videos()->first();
        if ($media) {
            return [
                'id' => $media->id,
                'thumb' => $media->thumb_url == "blank.jpg" ? null : asset('storage/product-knowledge/' . $media->thumb_url),
                'url' => $media->url == "blank.mp4" ? null : asset('storage/product-knowledge/' . $media->url),
                'new_url' => asset('storage/product-knowledge/' . $media->url)
            ];
        }
        return [];
    }
}
