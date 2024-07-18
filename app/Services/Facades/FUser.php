<?php


namespace App\Services\Facades;


use App\Helper\_EmailHelper;
use App\Helper\_RuleHelper;
use App\Helper\_SMSHelper;
use App\Models\Contact;
use App\Models\OtpCode;
use App\Models\Type;
use App\Models\User;
use App\Models\UserToken;
use App\Services\Interfaces\IUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class FUser extends FBase implements IUser
{

    public function __construct()
    {
        $this->model = User::class;
        $this->search = [];
        $this->hasUnique = true;
        $this->unique = "email";
        $this->hashing = true;
        $this->hashingColumn = "password";
        $this->encrypt = true;
        $this->verificationEmail = false;
        $this->encryptColumn = "password";
        $this->rules = [
            'name' => _RuleHelper::_Rule_Require,
            'phone' => _RuleHelper::_Rule_Require,
            'password' => _RuleHelper::_Rule_Require,
            'city_id' => _RuleHelper::_Rule_Require,
            'pharmacy_id' => _RuleHelper::_Rule_Require,
            'email' => _RuleHelper::_Rule_Require . "|" . _RuleHelper::_Rule_Email,
        ];

        $this->columns = ['name', 'password', 'email', 'phone', 'type_id', 'city_id', 'pharmacy_id', 'work_place'];
        $this->allColumns = ['name', 'phone', 'email', 'password', 'city_id', 'pharmacy_id', 'type_id', 'work_place'];
    }


    public function updateUser(Request $request, $id)
    {
        $rules = [
            'name' => _RuleHelper::_Rule_Require,
            'phone' => _RuleHelper::_Rule_Require,
            'city_id' => _RuleHelper::_Rule_Require,
            'pharmacy_id' => _RuleHelper::_Rule_Require,
            'email' => _RuleHelper::_Rule_Require . "|" . _RuleHelper::_Rule_Email,
        ];
        $ex = $request->validate($rules);
        if (($ex instanceof ValidationException)) {
            throw new ValidationException($ex->validator);
        }
        $item = $this->getById($id);
        if ($item) {
            if (!$this->checkDuplicate($request, $id)) {
                return null;
            }
            $item->update($this->getAllColumn($request));
            $item->save();
            return $item;
        }
        return null;
    }


    public function validationAll(Request $request)
    {
        try {
            $request->validate($this->rules);
            return true;
        } catch (ValidationException $exception) {
            return $exception;
        }
    }


    public function getByEmail($email)
    {
        return User::query()->where([
            'email' => $email
        ])->first();
    }

    public function login(Request $request)
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];
        $request->validate($rules);
        $user = $this->getByEmail($request->input('username'));
        if (!$user) {
            return null;
        }
        if (Hash::check($request->input('password'), $user->password) && $user->role != "user") {
            return $user;
        }
        return null;

    }

    public function init(Request $request)
    {
        $rules = [
            'phone' => 'required',
        ];
        $request->validate($rules);
        if (!$this->getByPhone($request->input('phone'))) {
            return User::query()->create([
                'phone' => $request->input('phone')
            ]);
        }
        throw new Exception("The account already exists.");
    }

    public function updateAllUserPassword(): bool
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->update([
                'password' => Hash::make($user->email)
            ]);
        }
        return true;
    }

    public function uploadImage(Request $request, $user_id)
    {
        $user = $this->getById($user_id);
        if ($request->hasFile('files')) {
            $images = $request->file('files');
            foreach ($images as $key => $image) {
                $destinationPath = public_path('storage/syndicates-thumb');
                $filename = date('Y-m-d') . "_syndicate_" . $user_id . "-" . $key . "." . $image->getClientOriginalExtension();
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(600, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $filename);
                $user->images()->create([
                    'url' => $filename,
                    'thumb_url' => $filename,
                    'mime_type' => 'image',
                    'type' => 'image',
                ]);
            }

            return true;
        }

        return false;
    }

    public function checkType(Request $request)
    {
        if ($request->has('type_id')) {
            $type = Type::query()->where('id', $request->type_id)->first();
            if ($type->document == 1) {
                return true;
            }
        }
        return false;
    }

    public function contact(Request $request)
    {
        try {

            $rules = [
                'email' => _RuleHelper::_Rule_Require . "|" . _RuleHelper::_Rule_Email,
                'full_name' => _RuleHelper::_Rule_Require,
                'message_text' => _RuleHelper::_Rule_Require
            ];

            $request->validate($rules);
            return Contact::query()->create([
                'full_name' => $request->input('full_name'),
                'email' => $request->input('email'),
                'message_text' => $request->input('message_text'),
                'user_id' => Auth::guard('api')->id()
            ]);
        } catch (Exception $exception) {
            throw new Exception($exception);
        }

    }

    public function forget(Request $request)
    {
        try {
            $rules = [
                'username' => _RuleHelper::_Rule_Require,
            ];
            $request->validate($rules);
            $user = $this->getByEmail($request->input('username'));
            if ($user) {
                $this->sendOtpToEmail($request->input('username'));
                return true;
            }
            return false;
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }

    public function reset(Request $request)
    {
        try {
            $rules = [
                'email' => _RuleHelper::_Rule_Require,
                'verify_code' => _RuleHelper::_Rule_Require,
                'password' => _RuleHelper::_Rule_Require,
            ];
            $request->validate($rules);
            $user = $this->getByEmail($request->input('email'));
            if ($user) {
                $check = $this->checkEmailOtp($request);
                if ($check) {
                    $user->update([
                        "password" => Hash::make($request->input('password'))
                    ]);
                    return true;
                }
            }
            return false;
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $id = Auth::guard('api')->id();

            $rules = [
                'password' => 'required',
                'confirm_password' => 'required',
            ];
            $request->validate($rules);
            $user = $this->getById($id);
            if ($user) {
                $user->update([
                    "password" => Hash::make($request->input('password'))
                ]);
                return $user;
            }
            return null;
        } catch (Exception $exception) {
            throw ($exception);
        }

    }

    public function checkToken($token)
    {
        return UserToken::query()->where([
            'token' => $token
        ])->where('expired_at', '>', Carbon::now())->first();

    }

    public function checkTokenWithoutExpired($token)
    {
        return UserToken::query()->where([
            'token' => $token
        ])->first();

    }

    public function getByPhone($phone)
    {
        return User::query()->where([
            'phone' => $phone
        ])->first();
    }


    public function verifyAccount(Request $request)
    {
        try {
            $rules = [
                'phone' => 'required',
                'otpPasscode' => 'required',
            ];
            $res = $this->getByPhone($request->input('phone'));
            if ($res) {

            }
            $this->checkCode($request->input('phone'), $request->input('otpPasscode'));


        } catch (Exception $exception) {
            return null;
        }
    }

    public function checkCode($phone, $otpPasscode)
    {
        $smsHelper = new _SMSHelper();
        list($code, $token) = $smsHelper->login();
//        $check = $smsHelper->checkCode($phone, $otpPasscode);
    }

    public function totalCount(): int
    {
        return User::query()->where('role', 'user')->count();
    }

    public function sendOtp($phone_number)
    {
        try {

            $smsHelper = new _SMSHelper();
            list($code, $token) = $smsHelper->login();
            $otp = OtpCode::query()->where([
                "phone_number" => $phone_number
            ])->where("expiry_date", ">=", Carbon::now())->first();
            if (!$otp) {
                $otp = OtpCode::query()->create([
                    "phone_number" => $phone_number,
                    "otp" => $this->generateRandomNumberString(6),
                    "expiry_date" => Carbon::now()->addMinutes(30)
                ]);
            }
            if ($otp && $code == 200) {
                $smsHelper->otp($phone_number, $token, $otp->otp);
                return $otp;
            }
            return null;
        } catch (Exception $exception) {
            Log::error($exception);
            return null;
        }
    }

    public function sendOtpToEmail($email)
    {

        try {
            $otp = OtpCode::query()->where([
                "email" => $email
            ])->where("expiry_date", ">=", Carbon::now())->first();
            if (!$otp) {
                $otp = OtpCode::query()->create([
                    "email" => $email,
                    "otp" => $this->generateRandomNumberString(6),
                    "expiry_date" => Carbon::now()->addHours(10)
                ]);
            }
            return _EmailHelper::sendOtpEmail($email, [
                'otp' => $otp->otp
            ], 'email.otp');
        } catch (Exception $exception) {
            Log::error($exception);
            return null;
        }
    }

    function generateRandomNumberString($length)
    {
        $characters = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function checkOtp(Request $request)
    {
        $rule = [
            "verify_code" => _RuleHelper::_Rule_Require,
            "phone" => _RuleHelper::_Rule_Require
        ];
        $request->validate($rule);
        return OtpCode::query()->where([
            "phone_number" => $request->input('phone'),
            "otp" => $request->input('verify_code')
        ])->where("expiry_date", ">=", Carbon::now())
            ->first();
    }

    public function checkEmailOtp(Request $request)
    {
        $rule = [
            "verify_code" => _RuleHelper::_Rule_Require,
            "email" => _RuleHelper::_Rule_Require
        ];
        $request->validate($rule);
        Log::error($request);
        return OtpCode::query()->where([
            "email" => $request->input('email'),
            "otp" => $request->input('verify_code')
        ])->where("expiry_date", ">=", Carbon::now())
            ->first();
    }

    public function setAvatar(Request $request)
    {
        try {
            $id = Auth::guard('api')->id();
            $rules = [
                'avatar' => 'required'
            ];
            $user = $this->getById($id);
            $request->validate($rules);
            if ($user) {
                $user->update([
                    'avatar' => $request->input('avatar')
                ]);
            }
            return $user;
        } catch (Exception $exception) {
            throw $exception;
        }
    }


}
