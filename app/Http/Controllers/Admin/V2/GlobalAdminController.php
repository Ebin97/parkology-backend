<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ThemeResource;
use App\Http\Resources\Admin\TypeResource;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IQuiz;
use App\Services\Interfaces\ITheme;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use App\Services\Interfaces\IUserQuiz;
use Exception;
use Illuminate\Http\Request;

class GlobalAdminController extends Controller
{

    private $user, $theme, $userQuiz, $quiz, $type;

    /**
     * @param IUser $user
     * @param ITheme $theme
     * @param IQuiz $quiz
     * @param IUserQuiz $userQuiz
     * @param IType $type
     */
    public function __construct(IUser $user, ITheme $theme, IQuiz $quiz, IUserQuiz $userQuiz, IType $type)
    {
        $this->user = $user;
        $this->theme = $theme;
        $this->quiz = $quiz;
        $this->userQuiz = $userQuiz;
        $this->type = $type;
    }

    public function home(Request $request): BaseResource
    {
        $users = $this->user->totalCount();
        $quizzes = $this->quiz->totalCount();
        $levels = $this->quiz->levelCount();

        return BaseResource::create([
            'users' => $users,
            'quizzes' => $quizzes,
            'levels' => $levels
        ]);
    }

    public function prepareQuiz(Request $request)
    {
        try {
            $themes = $this->theme->index($request);
            $types = $this->type->index($request);
            return BaseResource::create([
                'themes' => ThemeResource::dataCollection($themes),
                'types' => TypeResource::dataCollection($types),
            ]);

        } catch (Exception $e) {
            return BaseResource::returns();
        }

    }

}
