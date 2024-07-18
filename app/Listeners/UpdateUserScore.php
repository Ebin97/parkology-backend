<?php

namespace App\Listeners;

use App\Helper\_OneSignalHelper;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateUserScore
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $sale = $event->sale;
        $user_id = $sale->user_id;
        $items = $sale->items()->where([])->get();


        $packs = 0;
        foreach ($items as $item) {
            $packs += $item->packs;
        }
        $points = $this->getPoints($packs, $user_id);
        if ($points > 0) {
            if ($sale->status == "approved") {
                $receipt = $sale->receiptPoint()->first();
                if ($receipt) {
                    $receipt->score = $points;
                    $receipt->save();
                } else {
                    $sale->receiptPoint()->create([
                        'score' => $points,
                        'type' => 'receipt',
                        'status' => 1,
                        'user_id' => $user_id,
                    ]);
                }
            }

            $notification_title = "Parkology";
            $message = "";
            if ($sale->status == "approved") {
                $message = "Your Receipt #" . $this->getReceiptNumber($sale) . " (dated " . $sale->receipt_date . ") has been approved, granting you " . $points . " points.";
            } else if ($sale->status == "rejected") {
                $message = "Your Receipt #" . $this->getReceiptNumber($sale) . " (dated " . $sale->receipt_date . ") has been rejected";
            }
            $sale->receipt()->delete();
            if ($message != "") {
                $sale->receipt()->create([
                    'title' => $message,
                    'type' => 'sales',
                    'user_id' => $sale->user->id,
                    'status' => true
                ])->first();
                _OneSignalHelper::SendOnSignalMessageForList([
                    $sale->user->fcm
                ], -1, $notification_title, null, $message, 'sales', '', '', 'ios');
                _OneSignalHelper::SendOnSignalMessageForList([
                    $sale->user->fcm
                ], -1, $notification_title, null, $message, 'sales', '', '', 'android');
            }
        }
    }

    public function getReceiptNumber($obj)
    {
        $receiptYear = date('Y', strtotime($obj->receipt_date)); // Extract year from receipt date
        return 'PRV-' . $receiptYear . '-' . str_pad($obj->id, 5, '0', STR_PAD_LEFT);
    }

    public function getPoints($packs, $user_id)
    {
        $points = $packs * 5;
        if ($packs > 1) {
            // Points squared for additional packs
//            $points += pow(($packs - 1) * 5, 2);
            $points = pow($points, 2);
        }

        // 500 extra points for uploading receipts 12 days in a row
        if ($this->hasUploadedReceiptsFor12Days($user_id)) {
            $points += 500;
        }
        return $points;

    }

    private function hasUploadedReceiptsFor12Days($user_id)
    {
        // Calculate the start and end dates of the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Determine the midpoint of the month
        $midOfMonth = $startOfMonth->copy()->addDays(15);
        $isBeforeMidMonth = Carbon::now()->lte($midOfMonth);

        // Define the start and end dates based on whether today is before or after the midpoint of the month
        $startDate = $isBeforeMidMonth ? $startOfMonth : $midOfMonth;
        $endDate = $isBeforeMidMonth ? $midOfMonth : $endOfMonth;

        // Retrieve Sale records for the user within the last 15 days
        $sales = Sale::query()->where('user_id', $user_id)
            ->where(['active' => true])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();

        // Extract unique dates from the sales records
        $uniqueDates = $sales->pluck('created_at')->map(function ($date) {
            return $date->format('Y-m-d');
        })->unique()->toArray();

        // Check if the user has uploaded receipts on at least 12 different days
        $numberOfSalesWithUniqueDate = count($uniqueDates) >= 12;
        if ($numberOfSalesWithUniqueDate) {
            foreach ($sales as $sale) {
                $sale->active = false;
                $sale->save();
            }
        }
        return $numberOfSalesWithUniqueDate;
    }
}
