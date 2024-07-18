<?php


namespace App\Services\Facades;


use App\Helper\_RuleHelper;
use App\Models\Type;
use App\Services\Interfaces\IType;

class FType extends FBase implements IType
{
    public function __construct()
    {
        $this->model = Type::class;
        $this->search = ['name'];
        $this->translatable = true;
        $this->translatableColumn = ['name'];

        $this->slugging = "";
        $this->slug = false;

        $this->hasUnique = false;
        $this->encrypt = false;

        $this->verificationEmail = false;
        $this->rules = [
            'name' => _RuleHelper::_Rule_Require,
        ];
        $this->columns = ['name'];
    }
}
