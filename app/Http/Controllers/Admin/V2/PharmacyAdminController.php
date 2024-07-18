<?php

namespace App\Http\Controllers\Admin\V2;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PharmacyResource;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IPharmacy;
use Illuminate\Http\Request;

class PharmacyAdminController extends Controller
{
    private $pharmacy;

    public function __construct(IPharmacy $pharmacy)
    {
        $this->pharmacy = $pharmacy;
    }

    public function index(Request $request)
    {
        $res = $this->pharmacy->index($request);
        return PharmacyResource::collection($res);
    }

    public function store(Request $request)
    {
        try {
            $res = $this->pharmacy->store($request);
            if ($res) {
                return PharmacyResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function uploadCSV(Request $request)
    {
        try {
            $res = $this->pharmacy->upload($request);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {

            $res = $this->pharmacy->getById($id);
            if ($res) {
                return PharmacyResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $res = $this->pharmacy->update($request, $id);
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
            $res = $this->pharmacy->delete($id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (\Exception $exception) {
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        }

    }
}
