<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface IThemeLevel extends IBase
{
    public function storeWithQuiz(Request $request, $id);
}
