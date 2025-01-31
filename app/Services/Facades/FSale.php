<?php

namespace App\Services\Facades;

use App\Helper\_RuleHelper;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Interfaces\ISale;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class FSale extends FBase implements ISale
{
    public function __construct()
    {
        $this->model = Sale::class;
        $this->rules = [
            'receipt_date' => _RuleHelper::_Rule_Require,
            'receipt' => 'required|max:10240',
            'products' => 'required'
        ];

        $this->columns = ['receipt_date'];
    }

    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $wheres = [];
        if ($user->role == "user") {
            $wheres = [
                "user_id" => $user->id
            ];
        }
        $this->where = $wheres;
        return parent::index($request);
    }

    public function products(Request $request)
    {
        return Product::query()->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        try {
            $res = parent::store($request); // TODO: Change the autogenerated stub
            if ($res) {
                $user = Auth::guard('api')->user();
                $file = $request->file('receipt');
                $destinationPath = public_path('storage/receipt');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }
                $filename = date('Y-m-d-h-i-s') . "_receipt_" . $user->id . "." . $file->getClientOriginalExtension();
                $receipt = $this->uploadFile($res, $destinationPath, $file, $filename);
                if (!$receipt) {
                    throw new Exception("Receipt Not correct");
                }
                $products = $request->input('products');
                $products = json_decode($products);
                foreach ($products as $item) {
                    $product = $this->checkProduct($item->product_id);
                    if ($product) {
                        $columns = [
                            "product_id" => $product->id,
                            "receipt_id" => $res->id,
                            "user_id" => $user->id,
                            "packs" => $item->packs,
                        ];
                        SaleItem::query()->create($columns);
                    }
                }
                return $res;
            }
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }

    public function checkProduct($id)
    {
        return Product::query()->where(['id' => $id])->first();
    }

    public function changeStatus($receipt, $id, $status)
    {

        return SaleItem::query()->where([
            'id' => $id,
            'receipt_id' => $receipt
        ])->update([
            'status' => $status
        ]);
    }

    public function uploadFile($obj, $destinationPath, $image, $filename)
    {
        $imgFile = Image::make($image->getRealPath());
        $imgFile->save($destinationPath . '/' . $filename);
        return $obj->images()->create([
            'url' => $filename,
            'thumb_url' => $filename,
            'mime_type' => 'image',
            'type' => 'image',
        ]);
    }
}
