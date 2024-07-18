<?php

namespace App\Http\Controllers\Admin\V2;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ThemeLevelResource;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IQuiz;
use App\Services\Interfaces\IThemeLevel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizAdminController extends Controller
{
    private $level, $quiz;

    public function __construct(IThemeLevel $level, IQuiz $quiz)
    {
        $this->level = $level;
        $this->quiz = $quiz;
    }

    public function index(Request $request)
    {
        $res = $this->level->getByColumns([])->orderByRaw('CONVERT(level, SIGNED) desc')->get();
        try {
            return ThemeLevelResource::paginable($res);
        } catch (\Exception $e) {
            return BaseResource::returns();
        }
    }

    public function store(Request $request)
    {
        try {
            $res = $this->quiz->store($request);
            if ($res) {
                $this->quiz->quizType($request, $res);
                $themeLevel = $this->level->storeWithQuiz($request, $res->id);
                return ThemeLevelResource::create($themeLevel);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {

            $res = $this->quiz->getById($id);
            if ($res) {
                $level = $res->ThemeLevel()->first();
                if ($level) {
                    return ThemeLevelResource::create($level);
                }
            }
            return BaseResource::returns(_MessageHelper::NotExist, 404);
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $res = $this->quiz->update($request, $id);
            if ($res) {
                $level = $res->ThemeLevel()->first();
                if ($level) {
                    $this->level->update($request, $level->id);
                    return ThemeLevelResource::create($level);
                }
            }
            return BaseResource::returns(_MessageHelper::NotExist);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function toggleCorrect(Request $request, $id)
    {
        try {

            $res = $this->quiz->toggleCorrectAnswer($id, $request->answer_id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns();
        } catch (Exception $e) {
            return BaseResource::exception($e);
        }
    }


    public function destroy($id)
    {
        try {
            $res = $this->quiz->getById($id);

            if ($res) {
                $this->level->getByColumns(['quiz_id' => $res->id])->delete();
                $res->delete();
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

}
