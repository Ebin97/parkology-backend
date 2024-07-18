<?php

namespace App\Http\Resources\V2;

use App\Http\Resources\BaseResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class SaleResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'receipt_number' => $this->getReceiptNumber($this),
            'receipt_date' => Carbon::parse($this->receipt_date)->format('F jS, Y'),
            'items' => $this->productsItem($this),
            'points' => $this->points($this),
            'packs' => $this->packs($this),
            'status' => $this->receiptStatus($this),
            'media' => $this->media($this),
            'reasons' => $this->getReasons($this),
            'created_at' => date('Y-m-d h:iA', strtotime($this->created_at)),

        ];
    }

    public function getReceiptNumber($obj)
    {
        $receiptYear = date('Y', strtotime($obj->receipt_date)); // Extract year from receipt date
        return 'PRV-' . $receiptYear . '-' . str_pad($obj->id, 5, '0', STR_PAD_LEFT);
    }


    public function media($obj)
    {
        $receipt = $obj->images()->first();
        if ($receipt) {
            return asset('public/storage/receipt/' . $receipt->url);
        }
        return null;

    }

    public function productsItem($obj)
    {
        $list = [];
        $items = $obj->items()->get();
        foreach ($items as $item) {
            $list[] = [
                'id' => $item->id,
                'product' => $item->product->name,
//                'user' => $item->user->name,
                'packs' => $item->packs,
                'status' => $item->status,
                'points' => $this->getProductPoints($item->packs)
            ];
        }
        return $list;
    }

    public function getProductPoints($packs)
    {
        $points = $packs * 5;
        if ($packs > 1) {
            // Points squared for additional packs
            $points += pow(($packs - 1) * 5, 2);
        }
        return $points;
    }

    public function points($obj)
    {
        $user = $obj->user()->first();
        $status = $obj['status'];
        if ($status == "approved") {
            return $obj->receiptScore()->where([
                'scorable_id' => $obj->id,
                'user_id' => $user->id,
                'type' => 'receipt',
                'status' => true
            ])->sum('score');
        } else {
            return 0;
        }
    }

    public function packs($obj)
    {
        $user = $obj->user()->first();
        return (int)$obj->items()->where([
            'user_id' => $user->id,
        ])->sum('packs');
    }

    public function receiptStatus($obj)
    {
        $status = $obj['status'];
        return $status == "pending" ? "Under review" : ($status == "approved" ? "Approved" : "Rejected");

    }


    public function getReasons($obj)
    {
        $status = $obj['status'];
        $list = [];
        if ($status == "rejected") {
            $reasons = $obj->reasons()->get();
            foreach ($reasons as $item) {
                $list[] = $item->reason->title;
            }
        }
        return $list;
    }
}
