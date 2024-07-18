<?php


namespace App\Services\Interfaces;


use Illuminate\Http\Request;

interface IUser extends IBase
{
    public function login(Request $request);

    public function init(Request $request);


    public function updateUser(Request $request, $id);

    public function updateAllUserPassword();

    public function checkType(Request $request);

    public function verifyAccount(Request $request);

    public function uploadImage(Request $request, $user_id);

    public function contact(Request $request);

    public function checkToken($token);

    public function checkTokenWithoutExpired($token);

    public function forget(Request $request);

    public function reset(Request $request);

    public function updatePassword(Request $request);


    public function getByPhone($phone);
    public function getByEmail($email);

    public function totalCount();

    public function sendOtp($phone_number);

    public function sendOtpToEmail($email);

    public function checkOtp(Request $request);

    public function checkEmailOtp(Request $request);

    public function setAvatar(Request $request);


}
