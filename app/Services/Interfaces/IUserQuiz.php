<?php


namespace App\Services\Interfaces;


use Illuminate\Http\Request;

interface IUserQuiz extends IBase
{
    public function DailyChallenge(Request $request);

    public function getByUser($user_id, $level);

    public function leaderBoard(Request $request);

    public function getScore($level);

    public function getLastByUser($user_id);

    public function passed($quizzes);
}
