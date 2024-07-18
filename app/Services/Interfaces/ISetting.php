<?php


namespace App\Services\Interfaces;


use Illuminate\Http\Request;

interface ISetting extends IBase
{
    public function update(Request $request, $id);

    public function getSetting();

    public function delete($id);


}
