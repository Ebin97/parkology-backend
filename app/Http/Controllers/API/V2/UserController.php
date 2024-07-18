<?php

namespace App\Http\Controllers\API\V2;

use App\Helper\_EmailHelper;
use App\Helper\_GameHelper;
use App\Helper\_MessageHelper;
use App\Helper\_RuleHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\TypeResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\V2\CityResource;
use App\Http\Resources\V2\LeaderboardResource;
use App\Http\Resources\V2\LevelDetailsResource;
use App\Http\Resources\V2\LevelResource;
use App\Http\Resources\V2\NotificationResource;
use App\Http\Resources\V2\PharmacyResource;
use App\Http\Resources\V2\ProductKnowledgeResource;
use App\Http\Resources\V2\ThemeResource;
use App\Services\Interfaces\ICity;
use App\Services\Interfaces\INotification;
use App\Services\Interfaces\IPharmacy;
use App\Services\Interfaces\IProduct;
use App\Services\Interfaces\ITheme;
use App\Services\Interfaces\IType;
use App\Services\Interfaces\IUser;
use App\Services\Interfaces\IUserQuiz;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $user, $type, $city, $pharmacy, $theme, $quiz, $product, $notify;

    /**
     * @param IUser $user
     * @param IType $type
     * @param ICity $city
     * @param IPharmacy $pharmacy
     * @param IUserQuiz $quiz
     * @param ITheme $theme
     * @param IProduct $product
     * @param INotification $notify
     */
    public function __construct(IUser $user, IType $type, ICity $city, IPharmacy $pharmacy, ITheme $theme, IUserQuiz $quiz, IProduct $product, INotification $notify)
    {
        $this->user = $user;
        $this->type = $type;
        $this->city = $city;
        $this->pharmacy = $pharmacy;
        $this->theme = $theme;
        $this->quiz = $quiz;
        $this->product = $product;
        $this->notify = $notify;
    }

    public function cities(Request $request)
    {
        $cities = $this->city->getByColumns([])->orderBy('name')->get();
        return CityResource::collection($cities);
    }

    public function pharmacies(Request $request, $id)
    {
        $city = $this->city->getById($id);
        if ($city) {
            $pharmacies = $this->pharmacy->getByColumns([
                'city_id' => $city->id
            ])->get();
            return PharmacyResource::collection($pharmacies);
        }
        return PharmacyResource::collection([]);
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

    public function login(Request $request)
    {
        try {
            $rules = [
                'username' => 'required',
                'password' => 'required'
            ];
            $request->validate($rules);
            $res = $this->user->getByEmail($request->input('username'));
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

    public function verify(Request $request)
    {
        try {
            $check = $this->user->checkEmailOtp($request);
            if ($check) {
                $user = Auth::guard('api')->user();
                if ($user) {

                    $user->update([
                        'email_verified_at' => Carbon::now()
                    ]);
                    if ($user->document_verified == 1) {
                        return UserResource::create($user);
                    } else {
                        return BaseResource::returns(_MessageHelper::NotReviewed, 201);
                    }
                }
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return BaseResource::exception($exception);
        }
    }

    function store(Request $request)
    {
        try {
            $user = $this->user->store($request);
            if ($user) {
                return UserResource::create($user);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage(), 409);
        }
    }

    function forgot(Request $request)
    {
        try {
            $res = $this->user->forget($request);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function reset(Request $request)
    {
        try {
            $res = $this->user->reset($request);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    function sendSMS(Request $request)
    {
        try {
            $rule = [
                "email" => _RuleHelper::_Rule_Require
            ];
            $request->validate($rule);

            $res = $this->user->sendOtpToEmail($request->input('email'));
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 402);
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 402);
        }
    }

    public function completeInfo(Request $request, $id)
    {
        try {
            $res = $this->user->updateUser($request, $id);
            if ($res) {
                return UserResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::_Already_Exist, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            $res = $this->user->updateUser($request, $id);
            if ($res) {
                return UserResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $res = $this->user->updatePassword($request);
            if ($res) {
                return UserResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function updateAvatar(Request $request)
    {
        try {
            $res = $this->user->setAvatar($request);
            if ($res) {
                return UserResource::create($res);
            }
            return BaseResource::returns(_MessageHelper::ErrorInRequest, 400);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function finish(Request $request, $id)
    {
        try {
            $user = $this->user->getById($id);
            $rules = [
                'type_id' => _RuleHelper::_Rule_Require,
            ];
            $request->validate($rules);
            DB::beginTransaction();
            if ($user) {
                $type = $this->type->getById($request->input('type_id'));
                if ($type) {

                    if ($type->document) {
                        $rules = [
                            'files' => [_RuleHelper::_Rule_Require, 'array', 'size:2']
                        ];
                        $request->validate($rules);
                        $this->user->uploadImage($request, $user->id);
                        $media = $user->images()->get();
                        $token = _EmailHelper::generateToken($user, Carbon::now()->addYear());
                        if ($media) {
                            $urls = [];
                            foreach ($media as $item) {
                                $urls[] = public_path('storage/syndicates-thumb/' . $item->url);
                            }
                            _EmailHelper::sendSyndicateEmailToParkology($user, [
                                "id" => $user->id,
                                "name" => $user->name,
                                "token" => $token,
                                "email" => $user->email,
                                "city" => $user->City->name,
                                "role" => $type->name,
                                "pharmacy" => $user->Pharmacy->name,
                                "phone" => $user->phone,
                                "image" => "",
                            ], 'syndicate-verification', $urls);
                        }
                        $columns = [
                            'type_id' => $type->id,
                            'document_verified' => 0
                        ];
                        $user->update($columns);
                        DB::commit();
                        $this->user->sendOtpToEmail($user->email);
                        return BaseResource::returns(_MessageHelper::NotReviewed, 200);
                    } else {
                        $columns = [
                            'type_id' => $type->id,
                            'document_verified' => 1
                        ];
                        $user->update($columns);
                        DB::commit();
                        $this->user->sendOtp($user->phone);
                        return UserResource::create($user);
                    }
                }
            }
            DB::rollBack();
            return BaseResource::returns();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function userTypes(Request $request)
    {
        try {
            $res = $this->type->index($request);

            return TypeResource::collection($res);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());

        }
    }


    public function ads(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($request->has('fcm')) {
            $user->fcm = $request->input('fcm');
            $user->save();
        }
        return BaseResource::create([
            "profile" => UserResource::create($user),
            "ads" => [
                [
                    "id" => 1,
                    "url" => "https://img.freepik.com/premium-vector/cosmetic-fashion-sale-promotion-social-media-facebook-cover-banner-template_225928-53.jpg",
                    "action" => false,
                    "type" => null,
                ],
                [
                    "id" => 1,
                    "url" => "https://img.freepik.com/premium-vector/cosmetic-fashion-sale-promotion-social-media-facebook-cover-banner-template_225928-53.jpg",
                    "action" => true,
                    "type" => "Game",
                ],
            ]
        ]);
    }

    public function themePerPage(Request $request)
    {
        try {
            [$themeLevels, $themeLevel, $theme, $page, $numberOfPages] = $this->theme->getThemePerPage($request);


            return BaseResource::create([
                'levels' => LevelResource::dataCollection($themeLevels),
                'active_level' => $themeLevel ? LevelResource::create($themeLevel) : null,
                'theme' => ThemeResource::create($theme),
                'page' => $page,
                'next' => $page < $numberOfPages,
                'prev' => $page > 1
            ]);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function activeTheme(Request $request)
    {
        try {
            [$themeLevels, $themeLevel, $pageNumber, $numberOfPages] = $this->theme->active($request);
            $theme = null;
            if ($themeLevel) {
                $theme = ThemeResource::create($themeLevel->theme);
            }

            return BaseResource::create([
                'levels' => LevelResource::dataCollection($themeLevels),
                'active_level' => $theme ? LevelResource::create($themeLevel) : null,
                'page' => $pageNumber,
                'theme' => $theme,
                'next' => $pageNumber < $numberOfPages,
                'prev' => $pageNumber > 1
            ]);
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function activeLevel(Request $request)
    {
        try {
            [$code, $level] = $this->theme->activeLevel($request);
            switch ($code) {
                case 200:
                    if ($level) {
                        return LevelDetailsResource::create($level);
                    }
                    break;
                case 201:
                    return BaseResource::returns(trans("translate." . _MessageHelper::_Locked), 201);
                default:
                    break;
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            return BaseResource::returns($exception->getMessage());
        }
    }

    public function dailyQuiz(Request $request)
    {
        try {
            [$code, $res] = $this->theme->dailyQuiz($request);

            switch ($code) {
                case 200:
                    if ($res) {
                        $user = Auth::guard('api')->user();
                        return BaseResource::create([
                            'score' => $user->userScores()->sum('score')
                        ])->setMessage(trans("translate." . _MessageHelper::_Success));
                    }
                    break;
                case 201:
                    return BaseResource::returns(trans("translate." . _MessageHelper::_Solved), 201);
                case 407:
                    return BaseResource::returns(trans("translate." . _MessageHelper::_NotAvailable), 207);
                case 408:
                    return BaseResource::returns(trans("translate." . _MessageHelper::_NotCorrect), 208);
                case 409:
                    return BaseResource::returns(trans("translate." . _MessageHelper::_Lost), 209);
            }
            return BaseResource::returns(trans("translate." . _MessageHelper::ErrorInRequest));
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::exception($exception);
        }
    }

    public function leaderboard(Request $request)
    {
        try {
            $leaderboards = $this->quiz->leaderBoard($request);
            return LeaderboardResource::collection($leaderboards);
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::exception($exception);
        }
    }

    public function productKnowledge(Request $request)
    {
        try {
            $products = $this->product->index($request);
            return ProductKnowledgeResource::paginable($products);
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::exception($exception);
        }

    }

    public function productKnowledgeVideoWatched(Request $request, $id)
    {
        try {
            $user = Auth::guard('api')->user();
            $res = $this->product->watched($request, $id);
            if ($res) {
                return BaseResource::create([
                    'score' => $user->userScores()->sum('score'),
                    'points' => _GameHelper::_VideoScore()
                ]);
            }
            return BaseResource::returns();
        } catch (Exception $exception) {
            Log::error($exception);
            return BaseResource::exception($exception);
        }
    }

    public function notifications(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $res = $this->notify->getByColumns([])->where([
                'user_id' => $user->id
            ])->orWhereIn('type', [
                'public'
            ])->orderByDesc('created_at')->get();
            return NotificationResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function approveSyndicate(Request $request, $id, $token)
    {
        try {
            $userToken = $this->user->checkTokenWithoutExpired($token);

            if ($userToken) {
                $user = $userToken->User;
                if ($user) {
                    $user->document_verified = 1;
                    $user->save();

                    return "The Syndicate ID for " . $user->name . ' has been approved';
                }
            }

            return "Error in request";
        } catch (Exception $exception) {
            return "Error in request";
        }
    }

    public function rejectSyndicate(Request $request, $id, $token)
    {
        try {
            $userToken = $this->user->checkTokenWithoutExpired($token);
            if ($userToken) {
                $user = $userToken->User;
                if ($user) {
                    $user->document_verified = 0;
                    $user->save();
                }
                return "The Syndicate ID for " . $user->name . ' has been rejected';
            }

            return "Error in request";
        } catch (Exception $exception) {
            return "Error in request";
        }
    }

    public function aboutUs()
    {
        return BaseResource::create([
            'about' => "Parkville is an Egyptian Pharmaceutical Company with dreams to develop diversified solutions. With a clear and comprehensive understanding of patients’ needs, Parkville aims to manufacture and distribute unique products that can satisfy the needy for.

At Parkville, more than 450 employees flag and adopt enthusiasm. We cannot progress by staying the same. Enthusiasm is our precursor to inspiration and with that inspiration, we can achieve anything. We have learned this important lesson; that we have no choice but to progress.

We Grant care with every single product we provide.

As we move towards our goal of being a source of pride for every Egyptian customer, we support our employees to be the best they can be.

We consider our people as part owners of the business, and we energize that feeling as we believe that every member in Parkville family has the deep passion to lead us to highest standards in quality & efficiency.

Parkville goes beyond being a company- It’s a family where every family member shows deep respect for everyone, transparent and honest with our customers and with each other.

“In Parkville Community, you will believe that heroes exist”
",
        ]);
    }

    public function privacy()
    {
        return BaseResource::create([
            'privacy' => "Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of de Finibus Bonorum et Malorum (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, Lorem ipsum dolor sit amet.., comes from a line in section 1.10.32. The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from de Finibus Bonorum et Malorum by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.",
        ]);
    }

}
