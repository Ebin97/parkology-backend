<?php

namespace App\Services\Facades;

use App\Helper\_GameHelper;
use App\Helper\_RuleHelper;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Theme;
use App\Models\ThemeLevels;
use App\Models\UserQuiz;
use App\Services\Interfaces\ITheme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FTheme extends FBase implements ITheme
{
    const __PER_PAGE = 12;

    public function __construct()
    {
        $this->model = Theme::class;
        $this->rules = [
            'name' => _RuleHelper::_Rule_Require,
        ];
        $this->orderBy = 'orders';
        $this->columns = ['name'];
    }

    public function active(Request $request): ?array
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            $userLevel = $this->getLevel($user->id);
            return $this->getBulkLevels($userLevel);
        }
        return [[], null, 0, 0];
    }


    public function activeLevel(Request $request): array
    {
        return $this->getActiveLevel();
    }

    public function getActiveLevel(): array
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            $userLevel = $this->getLevel($user->id);
            $nextLevel = $this->getNextLevel(0, true);
            if ($userLevel) {
                $check = $userLevel->Answer->correct == 1 || $userLevel->attempts >= 3;
                $nextLevel = $this->getNextLevel($userLevel->quiz_id, $check);
            }
            if ($this->checkAvailableStatus($nextLevel->id)) {
                return [201, false];
            }
            return [200, $nextLevel];

        }
        return [400, false];
    }

    public function checkAvailableStatus($level_id)
    {
        $todayStart = Carbon::today()->startOfDay(); // Start of today (midnight)
        $todayEnd = Carbon::today()->endOfDay();     // End of today (23:59:59)
        $user = Auth::guard('api')->user();
        return UserQuiz::query()->where('level_id', '!=', $level_id)
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->exists();
    }

    function getLevel($id)
    {
        return UserQuiz::query()->where([
            'user_id' => $id,
        ])->orderByDesc('level_id')->first();
    }


    function getBulkLevels($userLevel): array
    {
        $themeLevel = $this->getNextLevel(0, true);
        if ($userLevel) {
            $check = $userLevel->Answer->correct == 1 || $userLevel->attempts >= 3;
            $themeLevel = $this->getNextLevel($userLevel->quiz_id, $check);
        }

        if ($themeLevel) {
            $currentRecord = $themeLevel->id;
            // Calculate the offset
            $offset = ($currentRecord - 1) % self::__PER_PAGE;
            // Calculate the starting record for the query
            $startRecord = $currentRecord - $offset;
            $pageNumber = ceil($themeLevel->level / self::__PER_PAGE);
            // Fetch records from ThemeLevel using the calculated offset
            $themeLevels = ThemeLevels::query()->where('active', 1)->orderBy('orders')->skip($startRecord - 1)->take(self::__PER_PAGE)->get();
            $numberOfPages = $this->numberOfPages();
            return [collect($themeLevels)->all(), $themeLevel, $pageNumber, $numberOfPages];
        }
        return [[], null, 0];
    }

    function numberOfPages(): int
    {
        $allLevels = ThemeLevels::query()->count();
        return ceil($allLevels / self::__PER_PAGE);
    }

    function getBulkLevelsPerPage($page): array
    {
        $user = Auth::guard('api')->user();
        // Fetch the next 12 levels for the current theme
        $levels = ThemeLevels::query()
            ->where('active', 1)
            ->orderBy('orders')
            ->skip(self::__PER_PAGE * ($page - 1))
            ->take(self::__PER_PAGE)
            ->get();
        $userLevel = $this->getLevel($user->id);
        $currentLevel = $this->getNextLevel(0, true);
        if ($userLevel) {
            $check = $userLevel->Answer->correct == 1 || $userLevel->attempts >= 3;
            $currentLevel = $this->getNextLevel($userLevel->quiz_id, $check);
        }
        $theme = null;
        if ($levels->isNotEmpty()) {
            $themeLevels = $levels->first();
            if ($themeLevels) {
                $theme = $themeLevels->theme;
            }
        }
        $numberOfPages = $this->numberOfPages();
        return [collect($levels)->all(), $currentLevel, $theme, $page, $numberOfPages];
    }

    public function dailyQuiz(Request $request)
    {
        $level_id = $request->input('level_id');
        $level = ThemeLevels::query()->where('id', $level_id)->first();
        $user = Auth::guard('api')->user();
        if ($level) {
            [$code, $check] = $this->checkLevelStatus($user->id, $level->id, $request->input('answer_id'));

            if ($code == 200 && $check) {
                $userQuiz = $this->updateOrCreateUserQuiz($request, $user->id, $level->id, $level->quiz_id);
                return [$code, $userQuiz];
            } else if ($code == 201) {
                return [$code, null];
            } else if ($code == 408) {
                $userQuiz = $this->updateOrCreateUserQuiz($request, $user->id, $level->id, $level->quiz_id);
                if ($userQuiz->attempts >= 3) {
                    return [409, $userQuiz];
                }
                return [$code, $userQuiz];
            }
            return [$code, null];
        }
        return [400, null];
    }

    function getNextLevel($id, $check)
    {
        return ThemeLevels::query()->where('active', 1)->where('quiz_id', function ($query) use ($id, $check) {
            $query->select('id')
                ->from('quizzes')
                ->where('id', $check ? '>' : '=', $id)
                ->orderBy('id')
                ->limit(1);
        })->first();
    }

    function getThemePerPage(Request $request): array
    {
        $user = Auth::guard('api')->user();
        $page = $request->input('page');
        if ($user) {
            return $this->getBulkLevelsPerPage($page);
        }
        return [[], null, null, 0];
    }

    function updateOrCreateUserQuiz(Request $request, $id, $level_id, $quiz_id)
    {
        $userQuiz = $this->getLevelByUserAndLevelId($id, $level_id);
        $answer = QuizAnswer::query()->where([
            'id' => $request->input('answer_id')
        ])->first();
        if (!$answer) {
            return null;
        }
        if (!$userQuiz) {
            $attempts = 1;
            $userQuiz = UserQuiz::query()->create([
                'level_id' => $level_id,
                'quiz_id' => $quiz_id,
                'user_id' => $id,
                'answer_id' => $answer->id,
                'attempts' => $attempts,
            ]);
        } else {
            $attempts = $userQuiz->attempts + 1;
            $userQuiz->update([
                'answer_id' => $answer->id,
                'attempts' => $attempts,
            ]);
        }
        if ($userQuiz && $userQuiz->Answer->correct == 1) {
            $userScore = $userQuiz->levelScore()->first();
            if ($userScore) {
                $userScore->update([
                    'score' => _GameHelper::_CalculateScore($userQuiz->attempts),
                    'status' => true
                ]);
            } else {
                $userQuiz->levelScore()->create([
                    'score' => _GameHelper::_CalculateScore($userQuiz->attempts),
                    'type' => 'quiz',
                    'user_id' => $id,
                    'status' => true
                ]);
            }
        }
        return $userQuiz;
    }

    function checkLevelStatus($id, $level_id, $answer_id)
    {

        if ($this->checkAvailableStatus($level_id)) {
            return [407, false];
        }
        $answer = QuizAnswer::query()->where('id', $answer_id)->first();
        $userQuiz = $this->getLevelByUserAndLevelId($id, $level_id);
        if ($userQuiz) {
            $prev_answer = $userQuiz->Answer;
            if ($userQuiz->attempts >= 3 && !$prev_answer->correct) {
                return [409, false];
            }

            if ($prev_answer && $prev_answer->correct) {
                return [201, false];
            }

        }
        if ($answer && !$answer->correct) {
            return [408, false];
        }

        return [200, true];

    }


    function getLevelByUserAndLevelId($id, $level_id)
    {
        return UserQuiz::query()->where([
            'user_id' => $id,
            'level_id' => $level_id,
        ])->first();
    }

}
