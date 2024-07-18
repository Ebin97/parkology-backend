<?php

namespace App\Http\Controllers\Admin;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Admin\SpecialityResource;
use App\Services\Interfaces\ISpeciality;
use Illuminate\Http\Request;

class SpecialityController extends Controller
{
    private $speciality;

    public function __construct(ISpeciality $speciality)
    {
        $this->speciality = $speciality;
    }

    public function index(Request $request)
    {
        $res = $this->speciality->index($request);
        return SpecialityResource::collection($res);
    }

    public function store(Request $request)
    {
        try {
            $res = $this->speciality->store($request);
            if ($res) {
                return SpecialityResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {

            $res = $this->speciality->getById($id);
            if ($res) {
                return SpecialityResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $res = $this->speciality->update($request, $id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (\Exception $exception) {
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        }
    }

    public function destroy($id)
    {
        try {
            $res = $this->speciality->delete($id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (\Exception $exception) {
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        }

    }
}
