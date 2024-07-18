<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;

class ProductKnowledgeResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'media' => $this->media($this)
        ];
    }

    function media($obj)
    {
        $media = $obj->videos()->first();
        if ($media) {
            return [
                'id' => $media->id,
                'thumb' => asset('public/storage/product-knowledge/' . $media->thumb_url),
                'url' => asset('public/storage/product-knowledge/' . $media->url),
            ];
        }
        return [];

    }
}
