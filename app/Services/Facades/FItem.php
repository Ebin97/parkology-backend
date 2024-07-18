<?php

namespace App\Services\Facades;

use App\Helper\_RuleHelper;
use App\Models\Product;
use App\Services\Interfaces\IItem;

class FItem extends FBase implements IItem
{
    public function __construct()
    {
        $this->model = Product::class;
        $this->rules = [
            'name' => _RuleHelper::_Rule_Require,
        ];
        $this->columns = ['name'];
    }


}
