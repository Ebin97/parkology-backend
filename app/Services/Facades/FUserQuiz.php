<?php

namespace App\Services\Facades;

use App\Models\UserQuiz;
use App\Models\UserScore;
use App\Services\Interfaces\IUserQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FUserQuiz extends FBase implements IUserQuiz
{

    public function __construct()
    {
        $this->model = UserQuiz::class;
        $this->translatable = false;
        $this->rules = [];
        $this->slugging = "";
        $this->slug = false;

        $this->hasUnique = false;
        $this->encrypt = false;

        $this->verificationEmail = false;
        $this->columns = ['quiz_id', 'answer_id', 'user_id', 'level_id', 'attempts'];
    }

    public function DailyChallenge(Request $request)
    {
        // TODO: Implement DailyChallenge() method.
    }

    public function getByUser($user_id, $level)
    {
        // TODO: Implement getByUser() method.
    }

    public function leaderBoard(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            return UserScore::query()
                ->join('users', 'users.id', '=', 'user_scores.user_id')
                ->where('users.type_id', '=', $user->type_id)
                ->select('user_scores.user_id', DB::raw('SUM(score) as total_score'))
                ->groupBy('user_scores.user_id')
                ->orderByDesc('total_score')
                ->take(10)
                ->get();
        }
        return [];
    }

    public function getScore($level)
    {
        // TODO: Implement getScore() method.
    }

    public function getLastByUser($user_id)
    {
        // TODO: Implement getLastByUser() method.
    }

    public function passed($quizzes)
    {
        // TODO: Implement passed() method.
    }
}
