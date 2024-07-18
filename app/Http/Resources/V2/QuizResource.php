<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;
use App\Models\UserQuiz;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class QuizResource extends BaseResource
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
            'slug' => $this->slug,
            'title' => $this->title,
            'answers' => $this->getAnswers($this->Answers)
        ];
    }

    public function getAnswers($answers): array
    {
        $list = [];
        foreach ($answers as $answer) {
            $list[] = [
                'id' => $answer->id,
                'slug' => $answer->slug,
                'title' => $answer->title,
                'correct' => $answer->correct,
                'quiz_id' => $answer->quiz_id,
                'orders' => $answer->orders,
            ];
        }
        return $list;
    }


}
