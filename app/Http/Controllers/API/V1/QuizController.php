<?php

namespace App\Http\Controllers\API\V1;

use App\Helper\_MessageHelper;
use App\Helper\_RuleHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\QuizResource;
use App\Services\Interfaces\IQuiz;
use App\Services\Interfaces\IUserQuiz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    private $quiz, $userQuiz;

    public function __construct(IQuiz $quiz, IUserQuiz $userQuiz)
    {
        $this->quiz = $quiz;
        $this->userQuiz = $userQuiz;
    }

    public function index(Request $request)
    {
        try {
            $res = $this->quiz->index($request);
            return QuizResource::collection($res);
        } catch (\Exception $exception) {
            return BaseResource::returns(trans("translate." . _MessageHelper::ErrorInRequest), 400);
        }
    }

    //Get current level and previous level details
    public function getLevel(Request $request)
    {

        $user = Auth::guard('api')->user();
        if ($user) {
            $userQuiz = $this->userQuiz->getLastByUser($user->id);
            $bonus = $this->quiz->checkBonus();
            if ($userQuiz) {
                $quiz = $this->quiz->getById($userQuiz->quiz_id);
                if ($quiz) {
                    $quizzes = $this->quiz->getQuizIDsByLevel($quiz->level);
                    $passed = $this->userQuiz->passed($quizzes);
                    $entry_date = Carbon::parse($userQuiz->created_at)->startOfDay();
                    $day = Carbon::now()->startOfDay()->diffInDays($entry_date);
                    if ($passed) {
                        $level = $quiz->level + 1;
                        $nextLevel = $this->quiz->getByLevel($level);
                        list($score, $stars) = $this->userQuiz->getScore($level);
                        if ($nextLevel) {
                            return BaseResource::create([
                                "level" => $level,
//                                "isActive" => ($day > 0),
                                "isActive" => true,
                                "stars" => $stars,
                                "score" => $score,
                                "bonus" => $bonus,
                                "previousLevel" => $this->getUserLevels($level)
                            ]);
                        }
                    }
                    list($score, $stars) = $this->userQuiz->getScore($quiz->level);
                    return BaseResource::create([
                        "level" => $quiz->level,
                        "isActive" => true,
                        "stars" => $stars,
                        "score" => $score,
                        "bonus" => $bonus,
                        "previousLevel" => $this->getUserLevels($quiz->level)
                    ]);
                }
            } else {
                list($score, $stars) = $this->userQuiz->getScore(1);
                $quiz = $this->quiz->getByLevel(1);
                if ($quiz) {
                    return BaseResource::create([
                        "level" => 1,
                        "score" => $score,
                        "isActive" => true,
                        "stars" => $stars,
                        "bonus" => $bonus,
                        "previousLevel" => []
                    ]);
                }
            }

        }
        return BaseResource::returns(trans("translate." . _MessageHelper::ErrorInRequest), 400);

    }

    public function getBonusQuiz(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            $quizzes = $this->quiz->getBonus();
            if (count($quizzes) > 0) return QuizResource::collection($quizzes);
            else return BaseResource::returns(trans("translate." . _MessageHelper::_NotAvailable), 400);
        }
        return BaseResource::returns(trans("translate." . _MessageHelper::ErrorInRequest), 400);
    }


    // - Daily Challenge
    public function getByLevel(Request $request)
    {
        try {

            list($level, $status) = $this->quiz->getLevel($request);
            // Log::error($level);
            if (count($level) > 0) {
                if ($status == _RuleHelper::_AVAILABLE) {
                    return QuizResource::collection($level);
                } else {
                    return BaseResource::returns(trans("translate." . _MessageHelper::_Locked), 409);
                }
            } else if ($status == _RuleHelper::_SOLVED) {
                return BaseResource::returns(trans("translate." . _MessageHelper::_Solved), 409);
            } else if ($status == _RuleHelper::_LOST) {
                return BaseResource::returns(trans("translate." . _MessageHelper::_Lost), 402);
            } else if ($status == _RuleHelper::_LOCKED) {
                return BaseResource::returns(trans("translate." . _MessageHelper::_Locked), 403);
            }
            return BaseResource::returns(trans("translate." . _MessageHelper::NotExist), 400);
        } catch (\Exception $exception) {
            Log::error($exception);
            return BaseResource::returns(trans("translate." . _MessageHelper::ErrorInRequest), 400);
        }
    }

    //Post Daily Challenge
    public function SubmitLevelAnswer(Request $request)
    {

        try {

            list($res, $status, $message) = $this->userQuiz->DailyChallenge($request);
            if ($res) {
                return BaseResource::ok(trans("translate." . $message));
            } else {
                if ($status) {
                    return BaseResource::returns(trans("translate." . $message), 201);
                } else {
                    return BaseResource::returns(trans("translate." . $message), 400);
                }
            }
        } catch (\Exception $exception) {
            return BaseResource::returns(trans("translate." . _MessageHelper::ErrorInRequest), 400);
        }
    }

    function getCurrentLevel()
    {

    }

    //Details for previous level details
    public function getUserLevels($level)
    {
        $user = Auth::guard('api')->user();
        return $this->userQuiz->getByUser($user->id, $level);

    }

    //Leader Board
    public function getLeaderBoard(Request $request)
    {
        $res = $this->userQuiz->leaderBoard($request);
        return BaseResource::collection($res);
    }
}
