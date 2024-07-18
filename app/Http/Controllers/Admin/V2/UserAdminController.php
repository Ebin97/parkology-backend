<?php

namespace App\Http\Controllers\Admin\V2;

use App\Helper\_MessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\BaseResource;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    private $user, $type;


    public function __construct(IUser $user, IType $type)
    {
        $this->user = $user;
        $this->type = $type;
    }

    public function index(Request $request)
    {
        $res = $this->user->getByColumns(['role' => 'user'])->orderByDesc('created_at')->get();
        try {
            return UserResource::paginable($res);
        } catch (\Exception $e) {
            return BaseResource::returns();
        }
    }

    public function store(Request $request)
    {
        try {

            $user = $this->user->store($request);
            if ($user) {
                return UserResource::create($user);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $this->user->update($request, $id);
            if ($user) {
                return UserResource::create($user);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (\Exception $exception) {
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        }
    }

    public function toggleDocument($id, $status)
    {
        $user = $this->user->getById($id);
        if ($user) {
            $user->update([
                'document_verified' => $status
            ]);
            return UserResource::create($user);
        }
        return BaseResource::returns(_MessageHelper::NotExist, 400);
    }

    public function show($id)
    {

        $user = $this->user->getById($id);
        if ($user) {
            return UserResource::create($user);
        }
        return BaseResource::returns(_MessageHelper::NotExist, 400);
    }

    public function destroy($id)
    {
        $user = $this->user->delete($id);
        if ($user) {
            return BaseResource::ok();
        }
        return BaseResource::returns(_MessageHelper::NotExist, 400);
    }
}
