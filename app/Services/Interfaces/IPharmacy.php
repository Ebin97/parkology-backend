<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface IPharmacy extends IBase
{
    public function upload(Request $request);
}
