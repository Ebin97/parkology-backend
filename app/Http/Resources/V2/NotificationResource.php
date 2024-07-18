<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class NotificationResource extends BaseResource
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
            'title' => $this->title,
            'type' => $this->type,
            'user_id' => $this->user_id,
            'status' => (bool)$this->status,
            'created_at' => Carbon::parse($this->created_at)->format('j F Y'),
        ];
    }
}
