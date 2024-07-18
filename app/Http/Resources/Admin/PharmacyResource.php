<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city_id' => $this->city_id,
            'city' => $this->city->name,

        ];
    }
}
