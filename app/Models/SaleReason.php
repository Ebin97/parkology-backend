<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReason extends Model
{
    use HasFactory;

    protected $fillable = ["reason_id", "sale_id"];

    public function reason()
    {
        return $this->belongsTo(RejectionReason::class, 'reason_id');
    }

    public function sale()
    {
        return $this->belongsTo(SaleReason::class, 'sale_id');
    }

}
