<?php

namespace App\Http\Controllers\API\V2;

use App\Events\SaleCreatedEvent;
use App\Helper\_EmailHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\V2\SaleResource;
use App\Models\RejectionReason;
use App\Models\SaleReason;
use App\Services\Interfaces\INotification;
use App\Services\Interfaces\ISale;
use App\Services\Interfaces\IUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    private $sale, $user, $notify;

    /**
     * @param ISale $sale
     * @param IUser $user
     * @param INotification $notify
     */
    public function __construct(ISale $sale, IUser $user, INotification $notify)
    {
        $this->sale = $sale;
        $this->user = $user;
        $this->notify = $notify;
    }


    public function rejectView(Request $request, $token, $id)
    {
        $sale = $this->sale->getById($id);
        $userToken = $this->user->checkTokenWithoutExpired($token);
        if ($sale && $userToken) {
            $reasons = RejectionReason::query()->get();


            return view('rejection-view')->with([
                'token' => $token,
                'id' => $id,
                'reasons' => $reasons,
                'sale' => (SaleResource::make($sale)->toArray($request))
            ]);
        } else {
            return view('404');
        }
    }

    public function rejectPost(Request $request, $id)
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
                foreach ($reasons as $item) {
                    $check = RejectionReason::query()->find($item);
                    if ($check) {
                        SaleReason::query()->create([
                            'sale_id' => $sale->id,
                            'reason_id' => $check->id,
                        ]);
                    }
                }
                $user = $sale->user()->first();
                event(new SaleCreatedEvent($sale));
                $media = $sale->images()->first();
                $token = _EmailHelper::generateToken($user, Carbon::now()->addYear());

                _EmailHelper::sendReceiptStatusEmailToUser($user, [
                    "id" => $sale->id,
                    "name" => $user->name,
                    "token" => $token,
                    "email" => $user->email,
                    "status" => "Rejected",
                    "url" => asset('public/storage/receipt/' . $media->url)
                ], 'receipt-status');

                return view('200');

            }
            return view('404');
        } catch (Exception $exception) {
            return view('404');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return BaseResource|JsonResponse|AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        try {
            $receipt = $this->sale->index($request);
            return SaleResource::paginable($receipt);
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::returns();
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return SaleResource|JsonResponse
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $res = $this->sale->store($request);
            if ($res) {
                // $user = Auth::guard('api')->user();
                // $token = _EmailHelper::generateToken($user, Carbon::now()->addYear());
                // $media = $res->images()->first();
                // $items = $res->items()->get();
                // $products = [];
                // foreach ($items as $item) {
                //     $products[] = [
                //         'id' => $item->id,
                //         'product' => $item->product->name,
                //         'packs' => (int)$item->packs,
                //     ];
                // }

                // _EmailHelper::sendReceiptEmailToParkology($user, [
                //     "id" => $res->id,
                //     "name" => $user->name,
                //     "token" => $token,
                //     "email" => $user->email,
                //     "url" => asset('public/storage/receipt/' . $media->url),
                //     "products" => $products,
                // ], 'receipt', [public_path('storage/receipt/' . $media->url)]);
                DB::commit();

                return SaleResource::create($res);
            }
            DB::rollBack();
            return BaseResource::returns();
        } catch (Exception $exception) {
            DB::rollBack();
            return BaseResource::exception($exception);

        }
    }

    public function approve($id, $token)
    {
        try {
            $userToken = $this->user->checkTokenWithoutExpired($token);
            $sale = $this->sale->getById($id);
            if ($sale) {
                if ($userToken) {
                    $user = $userToken->User;
                    $sale->status = "approved";
                    $sale->save();
                    $items = $sale->items()->where([])->get();
                    foreach ($items as $item) {
                        $item->status = "approved";
                        $item->save();
                    }
                    event(new SaleCreatedEvent($sale));
                    $media = $sale->images()->first();

                    _EmailHelper::sendReceiptStatusEmailToUser($user, [
                        "id" => $sale->id,
                        "name" => $user->name,
                        "token" => $token,
                        "email" => $user->email,
                        "status" => "Approved",
                        "url" => asset('public/storage/receipt/' . $media->url)
                    ], 'receipt-status');
                    return "The receipt for " . $user->name . ' has been approved';
                }
            }
            return "Error in request";
        } catch (Exception $exception) {
            return "Error in request";
        }
    }

    public function reject($id, $token)
    {
        try {
            $userToken = $this->user->checkTokenWithoutExpired($token);
            $sale = $this->sale->getById($id);
            if ($sale) {
                if ($userToken) {
                    $user = $userToken->User;
                    $sale->status = "rejected";
                    $sale->save();
                    $items = $sale->items()->where([])->get();
                    foreach ($items as $item) {
                        $item->status = "rejected";
                        $item->save();
                    }
                    event(new SaleCreatedEvent($sale));
                    $media = $sale->images()->first();

                    _EmailHelper::sendReceiptStatusEmailToUser($user, [
                        "id" => $sale->id,
                        "name" => $user->name,
                        "token" => $token,
                        "email" => $user->email,
                        "status" => "Rejected",
                        "url" => asset('public/storage/receipt/' . $media->url)
                    ], 'receipt-status');

                    return "The receipt for " . $user->name . ' has been rejected';
                }
            }
            return "Error in request";
        } catch (Exception $exception) {
            return "Error in request";
        }
    }

    public function updateStatus($id)
    {
        try {
            $sale = $this->sale->getById($id);

            if ($sale) {

            }
        } catch (Exception $exception) {

        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return BaseResource|JsonResponse
     */
    public function destroy($id)
    {
        try {
            $sale = $this->sale->delete($id);
            if ($sale) {
                return BaseResource::ok();
            }
            return BaseResource::returns("Error in request");
        } catch (Exception $exception) {
            return BaseResource::exception($exception);

        }
    }
}
