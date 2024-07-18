<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BaseResource;

class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $type = $this->Type;
        $pharmacy = $this->Pharmacy;
        $city = $this->City;
        return [
            'id' => $this->id,
            'name' => $this->name,

            'email' => $this->email,
            'active' => $this->hasVerifiedEmail(),
            'verified_at' => date('Y-m-d h:iA', strtotime($this->email_verified_at)) && $this->document_verified,
            'phone' => $this->phone,
            'pharmacy' => $pharmacy ? $pharmacy->name : '',
            'city' => $city ? $city->name : '',
            'type_id' => $type ? $type->id : null,
            'type' => $type ? $this->getType($this->Type, 'en') : null,
            'document_verified' => $this->document_verified ? ($type ? ($type->document ? "Verified" : "Not required") : 'Not attached') : "Not verified",
            'language' => $this->language,
            'role' => $this->role,
            'created_at' => date('Y-m-d h:iA', strtotime($this->created_at)),
            'media' => $this->getMedia($this)
        ];
    }

    function getMedia($user)
    {
        $list = [];
        $media = $user->images()->get();
        foreach ($media as $item) {
            $list[] = [
                'url' => asset('storage/syndicates-thumb/' . $item->url)
            ];
        }
        return $list;
    }


    function getType($type, $lang)
    {
        if ($type) {
            if ($lang == "both") {
                $language = "en";
            } else {
                $language = $lang ? $lang : "en";
            }
            return $type->getTranslation('name', $language);

        }
    }

}
