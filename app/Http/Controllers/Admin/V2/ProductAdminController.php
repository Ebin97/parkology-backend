<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IProduct;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductAdminController extends Controller
{
    private $product;

    /**
     * @param $product
     */
    public function __construct(IProduct $product)
    {
        $this->product = $product;
    }


    /**
     * Display a listing of the resource.
     *
     * @return BaseResource|JsonResponse|AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        try {
            $products = $this->product->getByColumns([])->orderByDesc('created_at')->get();
            return ProductResource::paginable($products);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ProductResource|JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $product = $this->product->store($request);
            if ($product) {
                return ProductResource::create($product);
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return ProductResource|JsonResponse
     */
    public function show(int $id)
    {
        try {
            $product = $this->product->getById($id);
            if ($product) {
                return ProductResource::create($product);
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return ProductResource|JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $product = $this->product->update($request, $id);
            if ($product) {
                return ProductResource::create($product);
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }


    public function uploadMedia(Request $request, $type, $id)
    {
        try {
            $product = $this->product->getById($id);
            DB::beginTransaction();
            if ($product) {
                if ($type == "image") {
                    $this->product->uploadImages($product, $request->file('files'), 'image');
                    DB::commit();
                    return ProductResource::create($product);
                } else if ($type == "video") {
                    $this->product->uploadVideo($product, $request->file('files'), 'video');
                    DB::commit();
                    return ProductResource::create($product);
                }
            }
            DB::rollBack();
            return BaseResource::returns();
        } catch (Exception $exception) {
            DB::rollBack();
            return BaseResource::exception($exception);
        }

    }

    public function destroyMedia(Request $request, $type, $id)
    {
        try {
            $product = $this->product->getById($id);
            DB::beginTransaction();
            if ($product) {
                if ($type == "image") {
                    $this->product->destroyImages($product);
                    DB::commit();
                    return ProductResource::create($product);
                } else if ($type == "video") {
                    $this->product->destroyVideo($product);
                    DB::commit();
                    return ProductResource::create($product);
                }
            }
            DB::rollBack();
            return BaseResource::returns();
        } catch (Exception $exception) {
            DB::rollBack();
            return BaseResource::exception($exception);
        }
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
            $product = $this->product->delete($id);
            if ($product) {
                return BaseResource::ok();
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }
}
