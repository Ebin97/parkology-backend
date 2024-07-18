<?php

namespace App\Http\Controllers\Admin;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TypeResource;
use App\Http\Resources\BaseResource;

use App\Services\Interfaces\IType;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    private $type;

    public function __construct(IType $type)
    {
        $this->type = $type;
    }

    public function index(Request $request)
    {
        $res = $this->type->index($request);
        return TypeResource::collection($res);
    }

    public function store(Request $request)
    {
        try {
            $res = $this->type->store($request);
            if ($res) {
                return TypeResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {

            $res = $this->type->getById($id);
            if ($res) {
                return TypeResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $res = $this->type->update($request, $id);
            if ($res) {
                return TypeResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (\Exception $exception) {
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        }
    }

    public function destroy($id)
    {
        try {
            $res = $this->type->delete($id);
            if ($res) {
                return TypeResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (\Exception $exception) {
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        }

    }
}
