<?php

namespace App\Services\Facades;

use App\Helper\_RuleHelper;
use App\Http\Resources\BaseResource;
use App\Models\City;
use App\Models\Pharmacy;
use App\Services\Interfaces\IPharmacy;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FPharmacy extends FBase implements IPharmacy
{

    public function __construct()
    {
        $this->model = Pharmacy::class;
        $this->rules = [
            'name' => _RuleHelper::_Rule_Require,
            'city_id' => _RuleHelper::_Rule_Require,
        ];
        $this->orderBy = 'orders';
        $this->columns = ['name', 'city_id', 'code'];
    }

    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, null, $delimiter)) !== false) {
                if (!$header)
                    $header = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $row);
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    public function upload(Request $request)
    {

        try {
            $rules = [
                'file' => _RuleHelper::_Rule_Require
            ];
            $request->validate($rules);
            $file = $request->file('file');
            $data = $this->csvToArray($file);

            foreach ($data as $key => $row) {
                $check = City::query()->where([
                    'name' => trim($row['city'])
                ])->first();
                if ($check) {
                    Pharmacy::query()->create([
                        'city_id' => $check->id,
                        'name' => trim($row['name']),
                        'code' => $row['code'],
                        'orders' => Pharmacy::query()->max('orders') + 1
                    ]);
                }
            }
            return BaseResource::ok();
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::returns();
        }
    }
}
