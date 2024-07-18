<?php


namespace App\Helper;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class _SMSHelper
{


    public function login()
    {
        $response = Http::accept('application/json')
            ->post('https://apis.cequens.com/auth/v1/tokens/', [
                'apiKey' => env('SMS_KEY'),
                'userName' => env('SMS_USERNAME'),
            ]);

        $data = $response->collect('data');
        Log::error($data);
        if ($response->status() == 200) {
            return [$response->status(), $data['access_token']];
        }
        return null;
    }

    public function otp($phone, $token, $code)
    {
        $response = Http::accept('application/json')
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json; charset=utf-8',
                'accept' => 'application/json',
            ])
            ->post('https://apis.cequens.com/sms/v1/messages', [
                'senderName' => 'Parkology',
                'messageText' => "Parkology verification code: " . $code,
                'messageType' => "text",
                'recipients' => $phone
            ]);
        Log::error($response);

        if ($response->status() == 200) {
            return true;
        }
        return null;
    }


}
