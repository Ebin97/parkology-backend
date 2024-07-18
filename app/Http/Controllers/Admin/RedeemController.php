<?php

namespace App\Http\Controllers\Admin;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RedeemResource;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IRedeem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedeemController extends Controller
{
    private $redeem;

    /**
     * @param IRedeem $redeem
     */
    public function __construct(IRedeem $redeem)
    {
        $this->redeem = $redeem;
    }


    /**
     * Display a listing of the resource.
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        $res = $this->redeem->index($request);
        return RedeemResource::paginable($res);
    }


    public function changeStatus(Request $request, $id)
    {
        try {
            $res = $this->redeem->toggleStatus($id);
            if ($res) {
                return BaseResource::ok();
            }
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return BaseResource|JsonResponse
     */
    public
    function destroy($id)
    {
        try {
            $res = $this->redeem->delete($id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 400);
        }
    }
}
