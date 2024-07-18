<?php

namespace App\Services\Facades;

use App\Helper\_RuleHelper;
use App\Models\Notifiaction;
use App\Services\Interfaces\INotification;

class FNotification extends FBase implements INotification
{
    public function __construct()
    {
        $this->model = Notifiaction::class;
        $this->rules = [
            'title' => _RuleHelper::_Rule_Require,
            'type' => _RuleHelper::_Rule_Require,
            'notifiable_id' => _RuleHelper::_Rule_Require,
            'notifiable_type' => _RuleHelper::_Rule_Require,
        ];
        $this->columns = ['title', 'type', 'notifiable_id', 'notifiable_type', 'user_id', 'status'];
    }
}
