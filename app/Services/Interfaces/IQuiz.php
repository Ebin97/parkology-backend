<?php


namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface IQuiz extends IBase
{
    function solved(Request $request);

    function getLevel(Request $request);

    function quizType(Request $request, $quiz_id);

    function getByLevel($level);
    function checkBonus();
    function getBonus();

    function checkLevel(Request $request);

    function getQuizIDsByLevel($level);

    function toggleCorrectAnswer($quiz_id, $id);

    public function totalCount();
    public function levelCount();

    public function totalNumberOfUserGroupedByLevel();

}
