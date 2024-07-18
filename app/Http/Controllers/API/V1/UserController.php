<?php

namespace App\Http\Controllers\API\V1;

use App\Helper\_EmailHelper;
use App\Helper\_MessageHelper;
use App\Helper\_RuleHelper;
use App\Helper\_SMSHelper;
use App\Helper\SMSResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\TypeResource;
use App\Http\Resources\UserResource;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\Interfaces\IRedeem;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{
    private $user, $type, $redeem;


    public function __construct(IUser $user, IType $type, IRedeem $redeem)
    {
        $this->user = $user;
        $this->type = $type;
        $this->redeem = $redeem;
    }


    public function Profile(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            return UserResource::create($user);
        } else {
            return BaseResource::ok();
        }
    }

    public function delete(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            if ($user) {
                $res = $this->user->delete($user->id);
                if ($res) {
                    return BaseResource::ok();
                }
                return BaseResource::returns(_MessageHelper::NotExist, 400);
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);

        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function adminLogin(Request $request)
    {
        try {
            $res = $this->user->login($request);
            if ($res) {
                return UserResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    public function login(Request $request)
    {
        try {
            $rules = [
                'username' => 'required',
                'password' => 'required'
            ];
            $request->validate($rules);
            $res = $this->user->getByPhone($request->username);
            if ($res) {
                if (Hash::check($request->input('password'), $res->password)) {
                    if ($res->email_verified_at) {
                        if ($res->document_verified == 1) {
                            return UserResource::create($res);
                        } else {
                            return BaseResource::returns(_MessageHelper::NotReviewed, 202);
                        }
                    } else {
                        $this->user->sendOtp($res->phone);
                        return UserResource::create($res)->setStatusCode(201);
                    }
                } else {
                    return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
                }
            }
            return BaseResource::returns(_MessageHelper::NotExist, 400);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    public function verifyAccount(Request $request)
    {
        try {
            $check = $this->user->checkOtp($request);

            if ($check) {
                $user = User::query()->where('phone', $check->phone_number)->first();
                if ($user->email_verified_at == null) {
                    $user->update([
                        'email_verified_at' => Carbon::now()
                    ]);
                    return BaseResource::ok();
                }
                return BaseResource::returns(_MessageHelper::AlreadyVerified);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    function sendSMS(Request $request)
    {
        try {
            $rule = [
                "phone" => _RuleHelper::_Rule_Require
            ];
            $request->validate($rule);
            $res = $this->user->sendOtp($request->input('phone'));
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 402);
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 402);
        }
    }

    public function redeem(Request $request)
    {
        try {
            list($status, $res) = $this->redeem->redeem($request);
            if ($status) {
                if ($res) {
                    return BaseResource::ok();
                }
                return BaseResource::returns(_MessageHelper::ErrorInRequest, 401);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    public function resend(Request $request)
    {
    }

    public function verify(Request $request)
    {
        try {

            $check = $this->user->checkOtp($request);

            if ($check) {
                $user = User::query()->where('phone_number', $check->phone_number)->first();
                if ($user->email_verified_at == null) {
                    $user->update([
                        'email_verified_at' => Carbon::now()
                    ]);
                    return BaseResource::ok();
                }
                return BaseResource::returns(_MessageHelper::AlreadyVerified);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return _MessageHelper::ErrorInRequest;
        }
    }

    public function store(Request $request)
    {
        try {
            if ($this->user->checkType($request)) {
                if ($request->hasFile('files')) {
                    $res = $this->user->store($request);
                    if ($res) {
                        $this->user->sendOtp($res->phone);
                        if ($res->Type->document) {
                            $this->user->uploadImage($request, $res->id);
                            $res->update([
                                'document_verified' => 0
                            ]);

                            $email = new _EmailHelper();
                            $media = $res->images()->first();
                            if ($media) {

                                $email->sendSyndicateEmailToParkology($res, [
                                    "first_name" => $res->first_name,
                                    "last_name" => $res->last_name,
                                    "email" => $res->email,
                                    "phone" => $res->phone,
                                    "image" => asset('storage/syndicates-thumb/' . $media->url),
                                ], 'syndicate-verification');
                            }
                        }
                        return UserResource::create($res);
                    }
                } else {
                    Log::error($request->file('files'));
                    return BaseResource::returns(_MessageHelper::_Fields_Validation, 400);
                }
            } else {
                $res = $this->user->store($request);
                if ($res) {
                    $this->user->sendOtp($res->phone);
                    return UserResource::create($res);
                }
            }
            return BaseResource::returns(_MessageHelper::_Already_Exist, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function profession(Request $request)
    {
        $cities = [

            'Cairo',
            'Giza',
            'Alexandria',
            'Dakahlia',
            'Red Sea',
            'Beheira',
            'Fayoum',
            'Gharbiya',
            'Ismailia',
            'Menofia',
            'Minya',
            'Qaliubiya',
            'New Valley',
            'Suez',
            'Aswan',
            'Assiut',
            'Beni Suef',
            'Port Said',
            'Damietta',
            'Sharkia',
            'South Sinai',
            'Kafr Al sheikh',
            'Matrouh',
            'Luxor',
            'Qena',
            'North Sinai',
            'Sohag'
        ];
        $types = $this->type->index($request);
        return BaseResource::create([
            'types' => TypeResource::collectionBody($types),
            'cities' => $cities
        ]);
    }

    public function approve($id, $token)
    {
        try {
            $redeem = $this->redeem->getById($id);
            $userToken = $this->user->checkTokenWithoutExpired($token);
            if ($redeem && $userToken) {
                if ($redeem->user_id == $userToken->user_id) {
                    $redeem->status = 0;
                    $redeem->request_status = "approved";
                    $redeem->save();
                    return "Redeem request approved";
                }
            } else {
                return "The details are not correct";

            }
        } catch (Exception $exception) {
            return "Opps! something wrong please try again ";

        }
    }

    public function refuse($id, $token)
    {
        try {
            $redeem = $this->redeem->getById($id);
            $userToken = $this->user->checkTokenWithoutExpired($token);
            if ($redeem && $userToken) {
                if ($redeem->user_id == $userToken->user_id) {
                    $redeem->status = 0;
                    $redeem->request_status = "rejected";
                    $redeem->save();
                    return "Redeem request rejected";
                }
            } else {
                return "The details are not correct";

            }
        } catch (Exception $exception) {
            return "Opps! something wrong please try again ";

        }


    }

    public function contact(Request $request)
    {
        try {
            $contact = $this->user->contact($request);
            if ($contact) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    public function update(Request $request)
    {
        try {
            $user_id = Auth::guard('api')->id();
            $res = $this->user->updateUser($request, $user_id);
            if ($res) {
                return UserResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }


    //Forget password

    public function forgetPassword(Request $request)
    {
        try {
            $res = $this->user->forget($request);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $rule = [
                'password' => _RuleHelper::_Rule_Require . "|required_with:confirm_password|same:confirm_password",
                'confirm_password' => _RuleHelper::_Rule_Require,
                'verify_code' => _RuleHelper::_Rule_Require,
                'phone'
            ];
            $request->validate($rule);

            $check = $this->user->checkOtp($request);
            $user = $this->user->getByPhone($request->input('phone'));
            if ($user && $check) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
                return BaseResource::ok();
            }

            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    public function forgetPasswordPost(Request $request, $token)
    {
        //        try {
        //            $check = $this->user->checkToken($token);
        //            if ($check) {
        //                $user = User::query()->where('id', $check->user_id)->first();
        //                if ($user) {
        //                    return view('user.reset-password')->with([
        //                        'token' => $check->token
        //                    ]);
        //                }
        //            }
        //        } catch (Exception $exception) {
        //            Log::error($exception->getMessage());
        //            return _MessageHelper::ErrorInRequest;
        //        }

    }

    public function updatePassword(Request $request, $token)
    {
        try {
            $rule = [
                'password' => _RuleHelper::_Rule_Require . "|confirmed",
            ];
            $request->validate($rule);
            $check = $this->user->checkToken($token);
            if ($check) {
                $user = User::query()->where('id', $check->user_id)->first();
                if ($user) {
                    $user->update([
                        'password' => Hash::make($request->password)
                    ]);
                    return "Your Password is changed successfully";
                }
            }
            return _MessageHelper::ErrorInRequest;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return _MessageHelper::ErrorInRequest;
        }
    }
}
