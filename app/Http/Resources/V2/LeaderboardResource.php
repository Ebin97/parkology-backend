<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class LeaderboardResource extends BaseResource
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
            'name' => $this->User->name,
            'score' => $this->total_score,
        ];
    }
}
