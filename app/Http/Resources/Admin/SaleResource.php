<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BaseResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'email' => $this->user->email,
            'status' => $this->receiptStatus($this),
            'receipt_number' => $this->getReceiptNumber($this),
            'receipt_date' => Carbon::parse($this->receipt_date)->format('j F Y'),
            'items' => $this->productsItem($this),
            'packs' => $this->packs($this),
            'points' => $this->points($this),
            'media' => $this->media($this),
            'reasons' => $this->getReasons($this),
            'created_at' => date('Y-m-d h:iA', strtotime($this->created_at)),
        ];
    }

    public function getReasons($obj)
    {
        $status = $obj['status'];
        $list = [];
        if ($status == "rejected") {
            $reasons = $obj->reasons()->get();
            foreach ($reasons as $item) {
                $list[] = [
                    'id' => $item->id,
                    'title' => $item->reason->title
                ];
            }
        }
        return $list;
    }

    public function receiptStatus($obj)
    {
        $status = $obj['status'];
        return $status == "pending" ? "Under review" : ($status == "approved" ? "Approved" : "Rejected");
    }

    public function getReceiptNumber($obj)
    {
        $receiptYear = date('Y', strtotime($obj->receipt_date)); // Extract year from receipt date
        return 'PRV-' . $receiptYear . '-' . str_pad($obj->id, 5, '0', STR_PAD_LEFT);

    }


    public function media($obj): ?string
    {
        $receipt = $obj->images()->first();
        if ($receipt) {
            return asset('storage/receipt/' . $receipt->url);
        }
        return null;

    }

    public function productsItem($obj): array
    {
        $list = [];
        $items = $obj->items()->get();
        foreach ($items as $item) {
            $list[] = [
                'id' => $item->id,
                'product' => $item->product->name,
//                'user' => $item->user->name,
                'packs' => $item->packs,

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
        $item = $obj->receiptPoint()->first();
        if ($item) {
            return $item->score;
        }
        return 0;
    }

    public function packs($obj)
    {
        $pack = $obj->items()->get()->sum('packs');
        return $pack . ($pack > 1 ? " packs" : " pack");
    }
}
