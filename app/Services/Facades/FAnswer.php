<?php


namespace App\Services\Facades;


use App\Helper\_RuleHelper;
use App\Models\QuizAnswer;
use App\Services\Interfaces\IAnswer;

class FAnswer extends FBase implements IAnswer
{
    public function __construct()
    {
        $this->model = QuizAnswer::class;
        $this->translatableColumn = ['title','slug'];
        $this->rules = [
            'title' => _RuleHelper::_Rule_Require,
            'quiz_id'=>_RuleHelper::_Rule_Require,
        ];
        $this->unique = "slug";
        $this->slug = true;
        $this->slugging = "title";
        $this->orderBy = 'orders';
        $this->columns = ['title', 'correct', 'orders', 'quiz_id','slug'];
    }

}
