<?php


namespace App\Services\Facades;


use App\Helper\_RuleHelper as _RuleHelperAlias;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizType;
use App\Models\ThemeLevels;
use App\Models\UserQuiz;
use App\Services\Interfaces\IQuiz;
use App\Services\Interfaces\IUserQuiz;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FQuiz extends FBase implements IQuiz
{
    private $userQuiz;

    public function __construct(IUserQuiz $userQuiz)
    {
        $this->model = Quiz::class;
        $this->translatableColumn = ['title', 'slug'];
        $this->rules = [
            'title' => _RuleHelperAlias::_Rule_Require,
        ];
        $this->where = [
            'bonus' => false
        ];
        $this->unique = "slug";
        $this->slug = true;
        $this->slugging = "title";
        $this->columns = ['title', 'bonus'];
        $this->userQuiz = $userQuiz;
    }

    function solved(Request $request)
    {
        // TODO: Implement solved() method.
    }

    //Prepare level for the game
    function getLevel(Request $request)
    {
        try {
            $request->validate([
                'level' => _RuleHelperAlias::_Rule_Require . '|' . _RuleHelperAlias::_Rule_Number
            ]);
            $user = Auth::guard('api')->user();
            $level = $request->input('level');

            if ($user) {
                $userQuiz = $this->userQuiz->getLastByUser($user->id);
                $check = true;

                if ($userQuiz) {
                    $entry_date = Carbon::parse($userQuiz->created_at)->startOfDay();
                    $check = $this->checkLevelOpen($entry_date);

                    // $day= Carbon::now()->diff($entry_date);
                }
                if ($check > 0 || $userQuiz->level == $level) {
                    $quizzes = $this->getByLevel($level);

                    list($lost, $correct) = $this->checkLevelForUser($quizzes);
                    if (count($lost) == 0 && count($correct) < count($quizzes)) {
                        return [$quizzes, _RuleHelperAlias::_AVAILABLE];
                    }

                    if (count($lost) == 0) {
                        return [[], _RuleHelperAlias::_SOLVED];
                    }
                    if (count($correct) < count($quizzes)) {
                        return [[], _RuleHelperAlias::_LOST];
                    }
                }
            }
            return [[], _RuleHelperAlias::_LOCKED];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    public function getBonus()
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            return $this->BonusQuery($user)->get();
        }
        return [];
    }

    public function BonusQuery($user): Builder
    {
        $userQuizzes = UserQuiz::query()->whereHas('Answer', function ($query) use ($user) {
            $query->where([
                'correct' => false
            ]);
        })->where([
            'user_id' => $user->id,
            'level' => 0,
            ['attempts', '<', 3]
        ])->get()->pluck('quiz_id')->toArray();
        return Quiz::query()->where([
            'level' => 0,
            'bonus' => true,
        ])->whereDoesntHave('UserQuizzes', function ($query) use ($user) {
            $query->where([
                'user_id' => $user->id,
                'level' => 0,
            ]);
        })->orWhereIn('id', $userQuizzes)->where([
            'level' => 0,
            'bonus' => true,
        ]);
    }

    public function checkBonus()
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            $quiz = $this->BonusQuery($user)->first();
            return (bool)$quiz;
        }
        return false;
    }

    public function checkLevelOpen($entryDate)
    {
        return 1;
//        return Carbon::now()->startOfDay()->diffInDays($entryDate);
    }

    public function quizType(Request $request, $quiz)
    {
        if ($request->has('types')) {
            foreach ($request->input('types') as $item) {
                $check = QuizType::query()->where([
                    'quiz_id' => $quiz->id,
                    'type_id' => $item,
                ])->first();
                if (!$check) {
                    QuizType::query()->create([
                        'quiz_id' => $quiz->id,
                        'type_id' => $item,
                        'level' => $quiz->level
                    ]);
                } else {
                    $check->update([
                        'level' => $quiz->level
                    ]);
                }
            }
        }
        return true;
    }

    function checkLevelForUser($quizzes)
    {
        $wrong = [];
        $correct = [];
        foreach ($quizzes as $quiz) {
            $check = UserQuiz::query()->where([
                'quiz_id' => $quiz->id,
                'user_id' => Auth::guard('api')->id(),
            ])->first();
            if ($check) {
                if ($check->attempts >= 3) {
                    $wrong[] = $check;
                } else {
                    if ($check->Answer->correct) {
                        $correct[] = $check;
                    }
                }
            }
        }
        return [$wrong, $correct];
    }

    function checkLevel(Request $request)
    {
        $userQuiz = $this->userQuiz->getLastByUser(Auth::guard('api')->id());

    }

    function getByLevel($level)
    {
        $user = Auth::guard('api')->user();
        // $quizzes_id = QuizType::query()->where([
        //     'level' => $level,
        //     'type_id' => $user->type_id
        // ])->pluck('quiz_id')->toArray();

        return Quiz::query()->where(['level' => $level])
            //->whereIn('id', $quizzes_id)
            ->where('bonus', false)
            ->get();
    }

    function getQuizIDsByLevel($level)
    {
        return Quiz::query()->where(['level' => $level])->where('bonus', false)
            ->pluck('id')->toArray();
    }

    function toggleCorrectAnswer($quiz_id, $id)
    {
        $quiz = $this->getById($quiz_id);

        if (!$quiz) {
            return null;
        }
        $quiz->answers()->update([
            'correct' => false
        ]);
        $quiz->answers()->where('id', '=', $id)->update([
            'correct' => true
        ]);
        return $quiz;
    }

    public function totalCount(): int
    {
        return Quiz::query()->count();
    }

    public function levelCount(): int
    {
        return ThemeLevels::query()->count();
    }

    public function totalNumberOfUserGroupedByLevel()
    {
//        $userQuiz=
    }
}
