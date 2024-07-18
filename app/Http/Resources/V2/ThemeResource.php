<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class ThemeResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->background($this),

        ];
    }

    public function background($obj): ?string
    {
        $image = $obj->images()->first();
        if ($image) {
            return asset('public/storage/maps/' . $image->url);
        }
        return null;
    }
}
