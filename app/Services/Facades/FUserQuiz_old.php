<?php


namespace App\Services\Facades;


use App\Helper\_MessageHelper;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Redeem;
use App\Models\UserQuiz;
use App\Services\Interfaces\IUserQuiz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FUserQuiz_old extends FBase implements IUserQuiz
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
        $this->columns = ['quiz_id', 'answer_id', 'user_id', 'attempts'];
    }


    public function DailyChallenge(Request $request)
    {
        try {
            $request->validate([
                'data' => 'required'
            ]);
            $status = true;
            foreach ($request->data as $item) {
                $quiz = null;
                $answer = null;
                if ($item['answer']) {
                    $answer = QuizAnswer::query()->where('id', $item['answer'])->first();
                    $check = $this->checkExist($answer->Quiz->id);
                    if ($answer) {
                        if (!$check) {
                            $res = UserQuiz::query()->create([
                                'quiz_id' => $answer->Quiz->id,
                                'answer_id' => $answer->id,
                                'level' => $answer->Quiz->level,
                                'user_id' => Auth::guard('api')->id()
                            ]);
                        } else {
                            if ($check->attempts > 3) {
                                return [false, false, _MessageHelper::_NotAvailable];
                            }
                            $check->update([
                                'answer_id' => $answer->id,
                                'attempts' => $check->attempts + 1,
                            ]);
                        }
                        if ($answer->correct) {
                            $status = $status & true;
                        } else {
                            $status = $status & false;
                        }
                    }
                }
            }
            if ($status) {
                return [true, true, _MessageHelper::_Solved];
            }
            return [false, true, _MessageHelper::_NotCorrect];
        } catch (\Exception $exception) {
            return [false, false, _MessageHelper::_NotAvailable];
        }
    }

    public function getByUser($user_id, $level)
    {
        $res = [];
        $records = UserQuiz::query()->where([
            'user_id' => $user_id
        ])->groupBy('level')->orderBy('level', 'desc')->pluck('level')->toArray();
        foreach ($records as $item) {
            if ($item != $level) {
                $userQuizzes = UserQuiz::query()->where('level', $item)->where('user_id', $user_id)->get();
                $check = true;
                $attempts = 0;
                $created_at = Carbon::now();

                foreach ($userQuizzes as $userQuiz) {
                    $attempts = $userQuiz->attempts;
                    $created_at = $userQuiz->created_at;
                    if ($userQuiz->attempts <= 3 && $userQuiz->Answer->correct == 1) {
                        $check = $check && true;
                    } else {
                        $check = $check && false;
                    }
                }

                $res[] = [
                    'level' => $item ? $item : -1,
                    'attempts' => 4 - $attempts, //for stars
                    'isPassed' => $check,
                    'isActive' => ($attempts < 3) && !$check,
                    'isFailed' => ($attempts >= 3) && !$check,
                    'created_at' => date('Y-m-d H:i:s', strtotime($created_at))
                ];
            }
        }
        return $res;
    }

    public function getLastByUser($user_id)
    {
        return UserQuiz::query()->where([
            'user_id' => $user_id,
            ['level', '>', 0]
        ])->latest('created_at')->first();
    }

    public function leaderBoard(Request $request)
    {
        $list = [];
        $res = UserQuiz::query()
            ->where('level', '!=', 0)
            ->selectRaw("count(*) as counts,level,sum(`attempts`) as score , `user_id`")
            //            ->selectRaw("count(*) as counts,sum(`attempts`) as attempts,((count(*)*3)-sum(`attempts`))*count(*) as score , `user_id`")
            ->orderBy('score', 'asc')
            ->groupBy("user_id", "level")->get();
        $res1 = UserQuiz::query()
            ->where('level', '=', 0)
            ->selectRaw("count(*) as counts,sum(`attempts`) as score , `user_id`")
            //            ->selectRaw("count(*) as counts,sum(`attempts`) as attempts,((count(*)*3)-sum(`attempts`))*count(*) as score , `user_id`")
            ->orderBy('score', 'asc')
            ->groupBy("user_id", "quiz_id")->get();
//        $res=$res->merge($res1);
        foreach ($res as $item) {
            $score = isset($list[$item->user_id]) ? $list[$item->user_id]['score'] : 0;
            if ($this->passedByLevel($item->level, $item->user_id)) {
                list($score, $stars) = $this->getNewScore($item->score, $score);
                $list[$item->user_id] = [
                    'score' => $score,
                    'user' => $item->User->first_name . " " . $item->User->last_name,
                    'email' => $item->User->email,
                    'me' => $item->User->id == Auth::guard('api')->id(),

                ];
            }
        }
        foreach ($res1 as $item) {
            $score = isset($list[$item->user_id]) ? $list[$item->user_id]['score'] : 0;
            if ($this->passedByQuiz($item->level, $item->user_id)) {
                list($score, $stars) = $this->getNewScore($item->score, $score);
                $list[$item->user_id] = [
                    'score' => $score,
                    'user' => $item->User->first_name . " " . $item->User->last_name,
                    'email' => $item->User->email,
                    'me' => $item->User->id == Auth::guard('api')->id(),

                ];
            }
        }

        return collect($list)->sortByDesc('score')->take(10)->toArray();
    }

    public function getNewScore($score, $oldScore, $oldStars = 0)
    {
        switch ($score) {
            case 1:
                $oldScore += 5;
                $oldStars += 3;
                break;
            case 2:
                $oldScore += 3;
                $oldStars += 2;
                break;
            case 3:
            default:
                $oldScore += 1;
                $oldStars += 1;
                break;
        }
        return [$oldScore, $oldStars];
    }

    public function getScore($level)
    {
        $score=0;
        $stars=0;
        $res = UserQuiz::query()->where(function ($query) {
            $query->whereIn('answer_id', QuizAnswer::query()->where('correct', true)->pluck('id')->toArray());
        })->where('user_id', Auth::guard('api')->id())->where('level', '!=', $level)
            ->selectRaw("count(*) as counts,level,(sum(`attempts`)/count(*)) as score")->where('level', '!=', '0')
            ->groupBy('level')->get();
        $res2 = UserQuiz::query()
            ->where(function ($query) {
                $query->whereIn('answer_id', QuizAnswer::query()->where('correct', true)->pluck('id')->toArray());
            })
            ->where('user_id', Auth::guard('api')->id())
            ->selectRaw("count(*) as counts,(sum(`attempts`)/count(*)) as score")->where('level', '0')
            ->groupBy('quiz_id')->get();

            foreach ($res as $item) {
                if ($this->passedByLevel($item->level, $item->user_id)) {
                    list($score, $stars) = $this->getNewScore($item->score, $score);

                }
            }
            foreach ($res2 as $item) {
                if ($this->passedByQuiz($item->level, $item->user_id)) {
                    list($score, $stars) = $this->getNewScore($item->score, $score);
                }
            }
        Log::error($score);
//            $score = $score1 + $score2;
//            $stars = $stars1 + $stars2;

//            $redeem = Redeem::query()->where([
//                'user_id' => Auth::guard('api')->id(),
//                'status' => true
//            ])->selectRaw("sum(points) as total_redeem")->groupBy('user_id')->first();
//            if ($redeem) {
//                $new_score = $score - $redeem->total_redeem;
//                if ($new_score >= 0) {
//                    $score = $new_score;
//                } else {
//                    $score = 0;
//                }
//            }
            return [(int)$score, (int)$stars];

    }

    public function calculateData($res)
    {
        $score = 0;
        $stars = 0;
        foreach ($res as $item) {
            if ($this->passedByLevel($item->level, Auth::guard('api')->id())) {
                switch ($item->score) {
                    case 1:
                        $score += 5;
                        $stars += 3;
                        break;
                    case 2:
                        $score += 3;
                        $stars += 2;
                        break;
                    case 3:
                        $score += 1;
                        $stars += 1;
                        break;
                    default:
                        break;
                }
            }
        }
        return [(int)$score, (int)$stars];

    }

    public function passed($quizzes)
    {
        $check = true;

        $userQuizzes = UserQuiz::query()->whereIn('quiz_id', $quizzes)->where('user_id', Auth::guard('api')->id())->get();
        foreach ($userQuizzes as $userQuiz) {
            if ($userQuiz->Answer->correct != 1 && $userQuiz->attempts < 3) {
                $check = false;
            }
        }
        return $check;
    }

    public function passedByLevel($level, $user_id)
    {
        $check = true;

        $userQuizzes = UserQuiz::query()->where('level', $level)->where('user_id', $user_id)->get();
        foreach ($userQuizzes as $userQuiz) {
            if ($userQuiz->Answer->correct != 1 || ($userQuiz->Answer->correct != 1 && $userQuiz->attempts == 3)) {
                $check = false;
            }
        }
        return $check;
    }

    public function passedByQuiz($quiz_id, $user_id)
    {
        $check = true;

        $userQuizzes = UserQuiz::query()->where('quiz_id', $quiz_id)->where('user_id', $user_id)->get();
        foreach ($userQuizzes as $userQuiz) {
            if ($userQuiz->Answer->correct != 1 || ($userQuiz->Answer->correct != 1 && $userQuiz->attempts == 3)) {
                $check = false;
            }
        }
        return $check;
    }

    public function checkExist($quiz_id)
    {
        return UserQuiz::query()->where([
            'quiz_id' => $quiz_id,
            'user_id' => Auth::guard('api')->id()
        ])->first();
    }
}
