<?php

namespace App\Services\Facades;

use App\Helper\_RuleHelper;
use App\Models\ThemeLevels;
use App\Services\Interfaces\IThemeLevel;
use Illuminate\Http\Request;

class FThemeLevel extends FBase implements IThemeLevel
{
    public function __construct()
    {
        $this->model = ThemeLevels::class;
        $this->rules = [
            'level' => _RuleHelper::_Rule_Require,
            'theme_id' => _RuleHelper::_Rule_Require,
        ];
        $this->columns = ['level', 'theme_id', 'active', 'orders'];
        $this->orderBy = "level";
    }

    public function storeWithQuiz(Request $request, $id)
    {
        $request->validate($this->rules);
        return ThemeLevels::query()->create([
            'title' => $request->input('title'),
            'level' => $request->input('level'),
            'theme_id' => $request->input('theme_id'),
            'quiz_id' => $id,
            'orders' => ThemeLevels::query()->max('orders') + 1
        ]);
    }
}
