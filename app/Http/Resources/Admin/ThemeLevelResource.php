<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeLevelResource extends BaseResource
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
            'level' => $this->level,
            'quiz_id' => $this->quiz->id,
            'title' => [
                'en' => $this->quiz->getTranslation('title', 'en'),
                'ar' => $this->quiz->getTranslation('title', 'ar'),
            ],
            'theme_id' => $this->theme_id,
            'active' => $this->active ? true : false,
            'is_active' => $this->active ? "Active" : "InActive",
            'quiz' => QuizResource::create($this->quiz),
            'created_at' => date('Y-m-d h:iA', strtotime($this->created_at)),
        ];
    }
}
