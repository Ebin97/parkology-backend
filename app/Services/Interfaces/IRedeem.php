<?php


namespace App\Services\Interfaces;


use Illuminate\Http\Request;

interface IRedeem extends IBase
{
    public function redeem(Request $request);
    public function toggleStatus($id);
}
