<?php


namespace App\Services\Interfaces;


use App\Models\Base;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use Illuminate\Http\Request;

interface IBase
{
    public function index(Request $request);

    public function getByColumns($columns);

    public function uploadFile($obj, $destinationPath, $image, $filename);

    public function store(Request $request);

    public function update(Request $request, $id);

    public function delete($id);

    public function getById($id);

    public function getBySlug($slug);


    public function getByDate(Request $request);


}
