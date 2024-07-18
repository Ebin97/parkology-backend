<?php

use App\Http\Controllers\Admin\V2\GlobalAdminController;
use App\Http\Controllers\Admin\V2\PharmacyAdminController;
use App\Http\Controllers\Admin\V2\ProductAdminController;
use App\Http\Controllers\Admin\V2\QuizAdminController;
use App\Http\Controllers\Admin\V2\QuizAnswerAdminController;
use App\Http\Controllers\Admin\V2\SaleAdminController;
use App\Http\Controllers\Admin\V2\TypeAdminController;
use App\Http\Controllers\Admin\GlobalController;
use App\Http\Controllers\Admin\QuizAnswerController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\RedeemController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\UserController as UserControllerAlias;
use App\Http\Controllers\Admin\V2\UserAdminController;
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V2\SaleController;
use App\Http\Controllers\API\V2\UserController as UserControllerV2;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * @return void
 */
function mainRoute(): void
{
    Route::post('/sendOtp', [UserController::class, 'sendSMS']);
    Route::post('/verify-account', [UserController::class, 'verifyAccount']);
    Route::post('/profile', [UserController::class, 'profile']);
    Route::post('forget-password', [UserController::class, 'forgetPassword']);
    Route::post('reset-password', [UserController::class, 'resetPassword']);
}


Route::group(['prefix' => 'v1', 'middleware' => ['language']], function () {
    mainRoute();
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/redeem/approve/{id}/{token}', [UserController::class, 'approve'])->name('redeem.approve');
    Route::get('/redeem/refuse/{id}/{token}', [UserController::class, 'refuse'])->name('redeem.refuse');
    Route::post('/register', [UserController::class, 'store']);

    //Get Route
    Route::get('profession', [UserController::class, 'profession']);
    Route::get('verify/account/{token}', [UserController::class, 'verify'])->name('verification.account');

    Route::get('approve/syndicate/{id}/{token}', [UserController::class, 'approve']);
    Route::get('reject/syndicate/{id}/{token}', [UserController::class, 'reject']);


    //Auth Route
    Route::group(['middleware' => ['auth:api', 'verified']], function () {
        //Active Level
        Route::get('active-level', [App\Http\Controllers\API\V1\QuizController::class, 'getLevel']);
        Route::get('bonus-quiz', [App\Http\Controllers\API\V1\QuizController::class, 'getBonusQuiz']);

        //Daily Challenge
        Route::get('daily-challenge', [App\Http\Controllers\API\V1\QuizController::class, 'getByLevel']);
        Route::post('daily-challenge', [App\Http\Controllers\API\V1\QuizController::class, 'SubmitLevelAnswer']);

        //Leader Board
        Route::get('leader-board', [App\Http\Controllers\API\V1\QuizController::class, 'getLeaderBoard']);
        //Contact us
        Route::post('contact', [UserController::class, 'contact']);
        //Profile
        Route::post('profile', [UserController::class, 'update']);
        Route::delete('account/delete', [UserController::class, 'delete']);

        //Products
        Route::get('brands', [ProductController::class, 'brands']);
        Route::get('brands/{brand_id}', [ProductController::class, 'products']);
        Route::get('products/{product_id}', [ProductController::class, 'details']);

        //Redeem
        Route::post('redeem', [UserController::class, 'redeem']);
    });

    Route::group(['prefix' => 'admin',], function () {
        Route::apiResource('quiz', QuizController::class);
        Route::post('/login', [UserController::class, 'adminLogin']);
        Route::post('/register', [UserController::class, 'store']);
        Route::get('/profile', [UserController::class, 'profile']);

        Route::group(['middleware' => ["auth:api"]], function () {
            Route::apiResource('type', TypeController::class);
            Route::apiResource('quiz', QuizController::class);
            Route::apiResource('answers', QuizAnswerController::class);
            Route::get('/redeem', [RedeemController::class, 'index']);
            Route::put('/redeem/{id}', [RedeemController::class, 'changeStatus']);
            Route::post('/quiz/{id}/toggleAnswer', [QuizController::class, 'toggleCorrect']);
            Route::get('/dashboard', [GlobalController::class, 'home']);


            Route::apiResource('users', UserControllerAlias::class);
            Route::post('toggleDocument/{id}/{status}', [UserControllerAlias::class, 'toggleDocument']);
        });
    });
});


Route::group(['prefix' => 'v2', 'middleware' => ['language']], function () {
    mainRoute();
    Route::post('/login', [UserControllerV2::class, 'login']);
    Route::get('cities', [UserControllerV2::class, 'cities']);
    Route::get('pharmacies/{city_id}', [UserControllerV2::class, 'pharmacies']);
    Route::post('/register', [UserControllerV2::class, 'store']);
    Route::post('/forgot-password', [UserControllerV2::class, 'forgot']);
    Route::post('/reset-password', [UserControllerV2::class, 'reset']);
    Route::get('/user-types', [UserControllerV2::class, 'userTypes']);
    Route::get('/about-us', [UserControllerV2::class, 'aboutUs']);
    Route::get('/privacy', [UserControllerV2::class, 'privacy']);
    Route::get('approve/syndicate/{id}/{token}', [UserControllerV2::class, 'approveSyndicate'])->name('approveSyndicate');
    Route::get('reject/syndicate/{id}/{token}', [UserControllerV2::class, 'rejectSyndicate'])->name('rejectSyndicate');
    Route::get('approve/receipt/{id}/{token}', [SaleController::class, 'approve'])->name('receipt-approved');
    Route::get('reject/receipt/{id}/{token}', [SaleController::class, 'reject'])->name('receipt-rejected');

    Route::group(['middleware' => ["auth:api"]], function () {
        Route::post('/update-profile/{id}', [UserControllerV2::class, 'updateProfile']);
        Route::post('/update-password', [UserControllerV2::class, 'updatePassword']);
        Route::post('/update-avatar', [UserControllerV2::class, 'updateAvatar']);
        Route::post('/complete-info/{id}', [UserControllerV2::class, 'completeInfo']);
        Route::post('/finish-registration/{id}', [UserControllerV2::class, 'finish']);
        Route::post('/send-otp', [UserControllerV2::class, 'sendSMS']);
        Route::post('/verify-otp', [UserControllerV2::class, 'verify']);
        Route::get('/ads', [UserControllerV2::class, 'ads']);
        Route::get('/active-theme', [UserControllerV2::class, 'activeTheme']);
        Route::get('/active-level', [UserControllerV2::class, 'activeLevel']);
        Route::get('/theme', [UserControllerV2::class, 'themePerPage']);
        Route::post('/daily-quiz', [UserControllerV2::class, 'dailyQuiz']);
        Route::get('/leaderboard', [UserControllerV2::class, 'leaderboard']);
        Route::get('/product-knowledge', [UserControllerV2::class, 'productKnowledge']);
        Route::post('/product-knowledge/{id}', [UserControllerV2::class, 'productKnowledgeVideoWatched']);
        Route::group(['prefix' => 'sales',], function () {
            Route::get('/', [SaleController::class, 'index']);
            Route::get('/products', [SaleAdminController::class, 'products']);
            Route::post('/', [SaleController::class, 'store']);
            Route::delete('/{id}', [SaleController::class, 'destroy']);
        });
        Route::get('/notifications', [UserControllerV2::class, 'notifications']);

    });
    Route::group(['prefix' => 'admin',], function () {

        Route::post('/login', [UserController::class, 'adminLogin']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::group(['middleware' => ["auth:api", "admin"]], function () {
            Route::get('/dashboard', [GlobalAdminController::class, 'home']);
            Route::get('/prepare-quiz', [GlobalAdminController::class, 'prepareQuiz']);
            Route::apiResource('type', TypeAdminController::class);
            Route::apiResource('quiz', QuizAdminController::class);
            Route::apiResource('answers', QuizAnswerAdminController::class);
            Route::apiResource('pharmacy', PharmacyAdminController::class);
            Route::apiResource('products', ProductAdminController::class);
            Route::apiResource('users', UserAdminController::class);
            Route::post('/pharmacy/upload', [PharmacyAdminController::class, 'uploadCSV']);
            Route::post('/products/{type}/upload/{id}', [ProductAdminController::class, 'uploadMedia']);
            Route::delete('/products/{type}/destroy/{id}', [ProductAdminController::class, 'destroyMedia']);
            Route::post('/quiz/{id}/toggleAnswer', [QuizAdminController::class, 'toggleCorrect']);
            Route::post('toggleDocument/{id}/{status}', [UserControllerAlias::class, 'toggleDocument']);


        });
        Route::group(['prefix' => 'sales',], function () {
            Route::get('/', [SaleAdminController::class, 'index']);
            Route::get('/rejection/reasons', [SaleAdminController::class, 'reasons']);
            Route::get('/products', [SaleAdminController::class, 'products']);
            Route::get('/{id}', [SaleAdminController::class, 'show']);
            Route::delete('/{id}', [SaleAdminController::class, 'destroy']);
            Route::post('/approved/{id}', [SaleAdminController::class, 'acceptReceipt'])->name('acceptReceipt');
            Route::post('/rejected/{id}', [SaleAdminController::class, 'rejectReceipt'])->name('rejectReceipt');
            Route::post('/acceptProduct/{receipt_id}/{id}', [SaleAdminController::class, 'acceptProductReceipt'])->name('acceptProductReceipt');
            Route::post('/rejectProduct/{receipt_id}/{id}', [SaleAdminController::class, 'rejectProductReceipt'])->name('rejectProductReceipt');
        });

    });

});
