<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;
use App\Models\UserQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelResource extends BaseResource
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
            'level' => $this->level,
            'quiz_id' => $this->quiz_id,
            'status' => $this->checkLevel($this),
            'pass' => $this->checkPass($this),
            'attempts' => $this->getAttemptsByUser($this),
        ];
    }

    public function checkPass($obj): int
    {
        $user = Auth::guard('api')->user();
        $check = UserQuiz::query()->where('quiz_id', $obj->quiz_id)->where('user_id', $user->id)->first();
        if ($check) {
            if ($check->Answer->correct)
                return 1;
            else return -1;
        }

        return 0;

    }

    public function checkLevel($obj): bool
    {
        $user = Auth::guard('api')->user();
        $check = UserQuiz::query()->where('user_id', $user->id)->where('answer_id', function ($query) use ($obj) {
            return $query->select('id')
                ->from('quiz_answers')
                ->where('quiz_id', $obj->quiz_id)
                ->where('correct', 1)
                ->first();
        })->first();
        if ($check) {
            return true;
        }

        return false;

    }


    public function getAttemptsByUser($level)
    {
        $check = UserQuiz::query()->where([
            'level_id' => $level->id,
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
