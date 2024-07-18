<?php


namespace App\Helper;


use Illuminate\Support\Facades\Log;

class _OneSignalHelper
{

    public static function SendOneSignalMessage($id, $title, $message, $type, $image, $url, $platform)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        // Your code here!
        $fields = array(
            'app_id' => $platform == "ios" ? getenv('ONESIGNAL_APP_ID_IOS') : getenv('ONESIGNAL_APP_ID'),
            'included_segments' => array(
                'All'
            ),
            'data' => [
                'id' => $id,
                'type' => $type,
            ],
            'contents' => array("en" => $message),
            'headings' => array("en" => $title),
            'largeIcon' => $image,
            'smallIcon' => 'ic_stat_onesignal_default',
            'big_picture' => $image,
            'url' => $url

        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $platform == "ios" ? getenv('ONESIGNAL_REST_API_KEY_IOS') : getenv('ONESIGNAL_REST_API_KEY'),
        ));


        $fields = json_encode($fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_encode($response);

    }


    public static function SendOnSignalMessageForList($list, $id, $title, $slug, $message, $type, $image, $url, $platform)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        // Your code here!
        $fields = array(
            'app_id' => $platform == "ios" ? getenv('ONESIGNAL_APP_ID_IOS') : getenv('ONESIGNAL_APP_ID'),
            'include_player_ids' => $list,
//            'include_external_user_ids' => $list,

            'data' => [
                'id' => $id,
                'type' => $type,
                'slug' => $slug,
            ],
            'contents' => array("en" => $message),
            'headings' => array("en" => $title),
            'smallIcon' => 'ic_stat_onesignal_default',
            'url' => $url

        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . ($platform == "ios" ? getenv('ONESIGNAL_REST_API_KEY_IOS') : getenv('ONESIGNAL_REST_API_KEY')),
        ));


        $fields = json_encode($fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_encode($response);
    }
}
