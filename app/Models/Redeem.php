<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Redeem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'points', 'voucher_number', 'status','request_status'];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
