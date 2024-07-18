<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface ITheme extends IBase
{
    public function active(Request $request);
    public function getThemePerPage(Request $request);
    public function activeLevel(Request $request);
    public function dailyQuiz(Request $request);


}
