<?php

namespace App\Services\Interfaces;


use Illuminate\Http\Request;

interface ISale extends IBase
{
    public function changeStatus($receipt, $id, $status);

    public function products(Request $request);
}
