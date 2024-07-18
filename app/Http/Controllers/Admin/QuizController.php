<?php

namespace App\Http\Controllers\Admin;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\QuizResource;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IQuiz;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    private $quiz;

    public function __construct(IQuiz $quiz)
    {
        $this->quiz = $quiz;
    }

    public function index(Request $request)
    {
        $res = $this->quiz->index($request);
        return QuizResource::collection($res);
    }

    public function store(Request $request)
    {
        try {
            $res = $this->quiz->store($request);
            if ($res) {
                $this->quiz->quizType($request, $res);
                return QuizResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function show( $id)
    {
        try {

            $res = $this->quiz->getById($id);
            if ($res) {
                return QuizResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (Exception $exception) {
		Log::error($exception);
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function update(Request $request,  $id)
    {
        try {

            $res = $this->quiz->update($request, $id);
            if ($res) {
                return QuizResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function toggleCorrect(Request $request, $id)
    {
        try {
            $res = $this->quiz->toggleCorrectAnswer($id, $request->answer_id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::return();
        } catch (Exception $e) {
            return BaseResource::exception($e);
        }
    }


    public function destroy( $id)
    {
        try {
            $res = $this->quiz->delete($id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

}
