<?php

namespace App\Http\Controllers\Admin\V2;

use App\Events\SaleCreatedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ReasonResource;
use App\Http\Resources\Admin\SaleResource;
use App\Http\Resources\BaseResource;
use App\Http\Resources\V2\ProductResource;
use App\Models\RejectionReason;
use App\Models\SaleReason;
use App\Services\Interfaces\ISale;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleAdminController extends Controller
{

    private $sale;

    /**
     * @param $sale
     */
    public function __construct(ISale $sale)
    {
        $this->sale = $sale;
    }


    /**
     * Display a listing of the resource.
     *
     * @return BaseResource|JsonResponse|AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        try {
            $res = $this->sale->index($request);
            return SaleResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function reasons()
    {
        try {
            $res = RejectionReason::query()->get();
            return ReasonResource::collection($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function products(Request $request)
    {
        try {
            $res = $this->sale->products($request);
            return ProductResource::collection($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return SaleResource|JsonResponse
     */
    public function show($id)
    {
        try {
            $sale = $this->sale->getById($id);
            return SaleResource::create($sale);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }


    //Accept all products on the receipt
    public function acceptReceipt($id)
    {
        try {
            DB::beginTransaction();
            $sale = $this->sale->getById($id);
            if ($sale) {
                $sale->status = "approved";
                $sale->save();
                SaleReason::query()->where([
                    'sale_id' => $sale->id,
                ])->delete();
                foreach ($sale->items()->get() as $item) {
                    $item->status = "approved";
                    $item->save();
                }

                event(new SaleCreatedEvent($sale));

                DB::commit();
                return BaseResource::ok();
            }
            DB::rollBack();
            return BaseResource::returns();
        } catch (Exception $exception) {
            DB::rollBack();
            return BaseResource::exception($exception);
        }
    }


    //Reject all product on the receipt
    public function rejectReceipt(Request $request, $id)
    {
        try {
            $request->validate([
                'reasons' => 'required'
            ]);
            $sale = $this->sale->getById($id);
            if ($sale) {
                $reasons = $request->input('reasons');
                $sale->status = "rejected";
                $sale->save();
                SaleReason::query()->where([
                    'sale_id' => $sale->id,
                ])->delete();
                foreach ($reasons as $item) {
                    $check = RejectionReason::query()->find($item);
                    if ($check) {
                        SaleReason::query()->create([
                            'sale_id' => $sale->id,
                            'reason_id' => $check->id,
                        ]);
                    }

                }
                foreach ($sale->items()->get() as $item) {
                    $item->status = "rejected";
                    $item->save();
                }
                event(new SaleCreatedEvent($sale));
                return BaseResource::ok();
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }


    //Accept product receipt by id
    public function acceptProductReceipt($receipt_id, $id)
    {
        try {
            $sale = $this->sale->getById($receipt_id);
            $status = $this->sale->changeStatus($receipt_id, $id, 'approved');
            if ($status) {
                if ($sale)
                    event(new SaleCreatedEvent($sale));
                return BaseResource::ok();
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    //Reject product receipt by id
    public function rejectProductReceipt($receipt_id, $id)
    {
        try {
            $sale = $this->sale->getById($receipt_id);
            $status = $this->sale->changeStatus($receipt_id, $id, 'rejected');
            if ($status) {
                if ($sale)
                    event(new SaleCreatedEvent($sale));
                return BaseResource::ok();
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
