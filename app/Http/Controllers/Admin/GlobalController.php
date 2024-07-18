<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\QuizResource;
use App\Http\Resources\UserResource;
use App\Services\Interfaces\IQuiz;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use App\Services\Interfaces\IUserQuiz;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    private $quiz, $userQuiz, $user, $type;

    /**
     * @param $quiz
     * @param $userQuiz
     * @param $user
     * @param $type
     */
    public function __construct(IQuiz $quiz, IUserQuiz $userQuiz, IUser $user, IType $type)
    {
        $this->quiz = $quiz;
        $this->userQuiz = $userQuiz;
        $this->user = $user;
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
}
