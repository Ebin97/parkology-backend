<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends BaseResource
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
            'id'=>$this->id,
            'title'=>$this->name,

        ];
    }
}
