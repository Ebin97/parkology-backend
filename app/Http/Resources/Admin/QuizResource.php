<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BaseResource;

class QuizResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable
     */
    public function toArray($request)
    {
        $answer = $this->Answers()->where(['correct' => true])->first();
        return [
            'id' => $this->id,
            'title' => [
                'en' => $this->getTranslation('title', 'en'),
                'ar' => $this->getTranslation('title', 'ar'),
            ],
            'slug' => $this->slug,
            'bonus' => $this->bonus,
            'active' => $this->active ?  true : false,
            'is_active' => $this->active ? "Active" : "InActive",

            'created_at' => date('Y-m-d h:iA', strtotime($this->created_at)),
            'correct_id' => $answer ? $answer->id : -1,
            'answers' => $this->getAnswers($this->Answers()->get())
        ];
    }

    public function getAnswers($answers)
    {
        $list = [];
        foreach ($answers as $answer) {
            $list[] = [
                'id' => $answer->id,
                'slug' => $answer->slug,
                'title' => [
                    'en' => $answer->getTranslation('title', 'en'),
                    'ar' => $answer->getTranslation('title', 'ar'),
                ],
                'correct' => $answer->correct,
                'quiz_id' => $answer->quiz_id,
                'orders' => $answer->orders,
            ];
        }
        return $list;
    }
}
