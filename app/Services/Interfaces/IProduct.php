<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface IProduct extends IBase
{

    public function watched(Request $request, $id);

    public function uploadVideo($object, $files, $type);

    public function destroyVideo($object);

    public function destroyImages($object);

}
