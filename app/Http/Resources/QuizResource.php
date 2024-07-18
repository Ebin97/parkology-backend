<?php

namespace App\Http\Resources;

use App\Models\UserQuiz;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'level' => $this->level,
            'slug' => $this->slug,
            'attempts' => $this->getAttemptsByUser($this->level),
            'answers' => $this->getAnswers($this->Answers)
        ];
    }

    public function getAnswers($answers)
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

//    public function getAttemptsByUser($quiz_id)
//    {
//        $check = UserQuiz::query()->where([
//            'quiz_id' => $quiz_id,
//            'user_id' => Auth::guard('api')->id()
//        ])->first();
//        if ($check) {
//            if ($check->attempts >= 3) {
//                return 3;
//            }
//            return $check->attempts;
//        }
//        return 0;
//    }


    public function getAttemptsByUser($level)
    {
        $check = UserQuiz::query()->where([
            'level' => $level,
            'user_id' => Auth::guard('api')->id()
        ])->get();
        $attempts = 0;
        foreach ($check as $item) {
            $attempts = $item->attempts;
        }
        if ($attempts >= 3) {
            $attempts = 3;
        }
        return 3 - $attempts;
    }
}
