<?php

namespace App\Http\Resources;


use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable
     */
    public function toArray($request)
    {
        $city = $this->City;
        $pharmacy = $this->Pharmacy;
        $type = $this->Type;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $city ? [
                "id" => $city->id,
                "name" => $city->name,
            ] : [],
            'pharmacy' => $pharmacy ? [
                "id" => $pharmacy->id,
                "name" => $pharmacy->name,
            ] : [],
            'type_id' => $type ? $type->id : null,
            'type' => $this->getType($this->Type, $this->language),
            'role' => $this->role,
            'token' => $this->createToken('API Token')->accessToken,
            'language' => $this->language,
            'score' => $this->getScore($this),
            'avatar' => $this->avatar,
            'verified' => $this->email_verified_at ? Carbon::parse($this->email_verified_at)->format('j F Y') : null
        ];
    }

    function getScore($obj)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $receipts = $obj->userScores()
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->where('type', 'receipt')
            ->sum('score');
        $levels = $obj->userScores()
            ->where('type', '!=', 'receipt')
            ->sum('score');
        return $receipts + $levels;
    }

    function getType($type, $lang)
    {
        if ($type) {
            if ($lang == "both") {
                $language = "en";
            } else {
                $language = $lang ?: "en";
            }
            return [
                'id' => $type->id,
                'slug' => $type->slug,
                'name' => $type->getTranslation('name', $language),
            ];
        }
    }
}
