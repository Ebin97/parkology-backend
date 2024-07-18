<?php

namespace App\Services\Facades;

use App\Helper\_GameHelper;
use App\Helper\_MediaHelper;
use App\Helper\_RuleHelper as _RuleHelperAlias;
use App\Models\ProductKnowledge;
use App\Models\UserScore;
use App\Services\Interfaces\IProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FProduct extends FBase implements IProduct
{
    public function __construct()
    {
        $this->model = ProductKnowledge::class;
        $this->translatableColumn = ['title', 'description'];
        $this->rules = [
            'title' => _RuleHelperAlias::_Rule_Require,
            'description' => _RuleHelperAlias::_Rule_Require,
            'active' => _RuleHelperAlias::_Rule_Require,
        ];
        $user = Auth::guard('api')->user();
        if ($user) {
            $this->where = [
                'active' => true,
                'type_id' => $user->type_id
            ];
        } else {
            $this->where = [
                'active' => true,
            ];
        }
        $this->columns = ['title', 'description', 'active',];
    }

    public function watched(Request $request, $id)
    {
        $product = $this->getById($id);
        $user = Auth::guard('api')->user();
        if ($product) {
            $check = UserScore::query()->where([
                'user_id' => $user->id,
                'type' => "product",
                'scorable_id' => $product->id
            ])->first();
            if (!$check) {
                return $product->productScore()->create([
                    'user_id' => $user->id,
                    'score' => _GameHelper::_VideoScore(),
                    'status' => true,
                    'type' => "product"
                ]);
            }
        }
        return null;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $product = ProductKnowledge::query()->create($this->getColumn($request));
        if ($product) {
            DB::commit();
        } else {
            DB::rollBack();
        }
        return $product;
    }

    public function uploadImages($object, $files, $type)
    {
        $slug = date('Y-m-d');
        $filename = $slug . "_product_knowledge_" . time() . "." . $files->getClientOriginalExtension();
        try {
            _MediaHelper::upload($files, $filename);
        } catch (Exception $e) {
            Log::error($e);
            return null;
        }
        $check = $object->videos()->first();
        if ($check) {
            $check->update([
                'thumb_url' => $filename
            ]);
        } else {
            $object->videos()->create([
                'url' => "blank.mp4",
                'thumb_url' => $filename,
                'mime_type' => 'video',
                'type' => 'video',
            ]);
        }
        return $filename;

    }

    public function uploadVideo($object, $files, $type)
    {

        $slug = date('Y-m-d');
        $filename = $slug . "_product_knowledge_" . time() . "." . $files->getClientOriginalExtension();
        try {
            _MediaHelper::uploadVideo($files, $filename);
        } catch (Exception $e) {
            Log::error($e);
            return null;
        }
        $check = $object->videos()->first();
        if ($check) {
            $check->update([
                "url" => $filename
            ]);
        } else {
            $object->videos()->create([
                'url' => $filename,
                'thumb_url' => "blank.jpg",
                'mime_type' => 'video',
                'type' => 'video',
            ]);
        }
        return $filename;

    }

    public function destroyImages($object)
    {
        $check = $object->videos()->first();
        if ($check) {
            $path = "storage/product-knowledge/" . $check->thumb_url;
            if (File::exists($path)) {
                File::delete($path);
            }
            $check->update([
                'thumb_url' => 'blank.jpg',
            ]);
            return true;
        }
        return false;
    }

    public function destroyVideo($object)
    {
        $check = $object->videos()->first();
        if ($check) {
            $path = "storage/product-knowledge/" . $check->url;
            if (File::exists($path)) {
                File::delete($path);
            }
            $check->update([
                'url' => 'blank.mp4',
            ]);
            return true;
        }
        return false;
    }
}
