<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LeaderBoardResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable
     */
    public function toArray($request)
    {
        return [
            'counts' => $this->counts,
            'attempts' => $this->attempts,
            'score' => $this->score,
            'user' => $this->User->first_name." ".$this->User->last_name,
            'email' => $this->User->email,
            'me' => $this->User->id == Auth::guard('api')->id(),

        ];

    }
}
