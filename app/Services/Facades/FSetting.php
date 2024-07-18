<?php


namespace App\Services\Facades;


use App\Helper\_RuleHelper;
use App\Models\Settings;
use App\Services\Interfaces\ISetting;

class FSetting extends FBase implements ISetting
{
    public function __construct()
    {
        $this->model = Settings::class;
        $this->search = [];
        $this->translatableColumn = [];
        $this->rules = [];
        $this->slug = false;
        $this->columns = ['api_status'];
    }

    public function getSetting()
    {
        return Settings::query()->first();
    }
}
