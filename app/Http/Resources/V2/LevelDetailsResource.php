<?php

namespace App\Http\Resources\V2;

use App\Helper\_GameHelper;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LevelDetailsResource extends BaseResource
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
            'quiz_id' => $this->Quiz->id,
            'slug' => $this->Quiz->slug,
            'level' => $this->level,
            'title' => $this->Quiz->title,
            'attempts' => $this->getAttempts($this),
            'answers' => $this->getAnswers($this->Quiz->Answers()->orderByDesc('orders')->get()),

        ];
    }

    public function getAttempts($obj)
    {
        $attempts = 0;
        $user = Auth::guard('api')->user();
        if ($user) {
            $check = $obj->userQuiz()->where([
                'user_id' => $user->id,
                'quiz_id' => $obj->Quiz->id
            ])->first();
            if ($check) {
                return _GameHelper::_CalculateLife($check->attempts);
            } else {
                return _GameHelper::_CalculateLife(0);
            }
        }
        return $attempts;

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
