<?php

namespace App\Services\Facades;

use App\Helper\_RuleHelper;
use App\Models\City;
use App\Services\Interfaces\ICity;

class FCity extends FBase implements ICity
{
    public function __construct()
    {
        $this->model = City::class;
        $this->rules = [
            'name' => _RuleHelper::_Rule_Require,
        ];
        $this->orderBy = 'created_at';
        $this->columns = ['name'];
    }
}
