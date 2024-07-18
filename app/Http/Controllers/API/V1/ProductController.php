<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use Illuminate\Support\Facades\Request;

class ProductController extends Controller
{
    public function brands()
    {

        $res = [
            [
                'id' => 1,
                'title' => 'seropipe',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/seropipe-brand-logo-155x60.png'
            ],
            [
                'id' => 2,
                'title' => 'bobai',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/bobai-brand-logo-155x60.png'
            ],
            [
                'id' => 3,
                'title' => 'starville',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/starville-brand-logo-155x60.png'
            ],
            [
                'id' => 4,
                'title' => 'strongville',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/strongville-brand-logo-1-155x60.png'
            ],
            [
                'id' => 5,
                'title' => 'shaan',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/shaan-brand-logo-155x60.png'
            ],

        ];
        return BaseResource::collection($res);
    }

    public function products(Request $request, $brand)
    {
        $brandDetails = [
            'id' => 1,
            'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/seropipe-brand-logo-155x60.png',
            'description' => 'Seropipe Hair Care Products Rich Contains Multiple Natural Japanese Herbs (Kamigen K & Kamigen E) And Vitamins To Control Different Hair Problems With Hair Routine Solution',

        ];
        $res = [
            [
                'id' => 1,
                'title' => 'seropipe product',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/seropipe-brand-logo-155x60.png',
                'brand_id' => 1
            ],
            [
                'id' => 2,
                'title' => 'bobai product',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/bobai-brand-logo-155x60.png',
                'brand_id' => 2
            ],
            [
                'id' => 3,
                'title' => 'starville product',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/starville-brand-logo-155x60.png',
                'brand_id' => 3

            ],
            [
                'id' => 4,
                'title' => 'strongville product',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/strongville-brand-logo-1-155x60.png',
                'brand_id' => 4

            ],
            [
                'id' => 5,
                'title' => 'shaan product',
                'image' => 'https://parkvillepharma.com/wp-content/uploads/2020/07/shaan-brand-logo-155x60.png',
                'brand_id' => 5
            ],
        ];
        return BaseResource::collection($res);
    }

    public function details()
    {
        return BaseResource::ok();

    }
}
