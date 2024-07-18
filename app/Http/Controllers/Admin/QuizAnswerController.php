<?php

namespace App\Http\Controllers\Admin;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuizAnswerController extends Controller
{
    private $answer;

    /**
     * QuizAnswerController constructor.
     * @param $answer
     */
    public function __construct(IAnswer $answer)
    {
        $this->answer = $answer;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $res = $this->answer->index($request);
        dd($res);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return BaseResource|JsonResponse
     */
    public function store(Request $request)
    {
        $res = $this->answer->store($request);
        if ($res) {
            return BaseResource::ok();
        }
        return BaseResource::returns(_MessageHelper::NotExist, 400);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $res = $this->answer->getById($id);
        dd($res);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return BaseResource|JsonResponse
     */
    public function update(Request $request, $id)
    {
        $res = $this->answer->update($request, $id);
        if ($res) {
            return BaseResource::ok();
        }
        return BaseResource::returns(_MessageHelper::NotExist, 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $admin_id
     * @param int $id
     * @return BaseResource|JsonResponse
     */
    public function destroy( $id)
    {
        $res = $this->answer->delete($id);
        if ($res) {
            return BaseResource::ok();
        }
        return BaseResource::returns(_MessageHelper::NotExist, 400);
    }
}
