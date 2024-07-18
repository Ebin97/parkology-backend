<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BaseResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RedeemResource extends BaseResource
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
            'user_name' => $this->User->first_name . " " . $this->User->last_name,
            'voucher_number' => $this->voucher_number,
            'status' => $this->status == 1 ? "Pending" : "Done",
            'created_at' => date('Y-m-d h:iA', strtotime($this->created_at)),
        ];
    }
}
