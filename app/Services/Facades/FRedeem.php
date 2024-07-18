<?php


namespace App\Services\Facades;


use App\Helper\_EmailHelper;
use App\Helper\_RuleHelper as _RuleHelperAlias;
use App\Models\Redeem;
use App\Services\Interfaces\IRedeem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FRedeem extends FBase implements IRedeem
{
    public function __construct()
    {
        $this->model = Redeem::class;
        $this->translatableColumn = [];
        $this->rules = [
            'points' => _RuleHelperAlias::_Rule_Require,
        ];
        $this->columns = ['user_id', 'points'];
    }

    public function redeem(Request $request)
    {

        $check = $request->validate([
            'points' => _RuleHelperAlias::_Rule_Require,
        ]);
        if ($check) {
            $voucher = $this->generateRandomString(10);
            $res = Redeem::query()->create([
                'points' => $request->points,
                'user_id' => Auth::guard('api')->id(),
                'voucher_number' => $voucher
            ]);
            if ($res) {
                $user = Auth::guard('api')->user();
                $email = new _EmailHelper();

                $email->sendEmailToParkology($user, [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'voucher_number' => $voucher,
                    'points' => $res->points,
                    'token' => $email->generateToken($user, Carbon::now()->addDay(15)),
                    'id' => $res->id,
                ], 'redeem-parkology-team');
                $email->sendEmail($user, [
                    'voucher_number' => $voucher,
                    'points' => $res->points,
                    'created_at' => date('Y-m-d h:iA', strtotime($res->created_at))
                ], 'redeem');
                return [true, $res];
            }
            return [true, $res];
        }
        return [false, null];

    }

    public function generateRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function toggleStatus($id): int
    {
        return Redeem::query()->where([
            'id' => $id
        ])->update([
            'status' => false
        ]);
    }
}
