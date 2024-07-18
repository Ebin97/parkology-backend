<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserScore extends Model
{
    use HasFactory;

    protected $fillable = ["score", "status", "type", "user_id", "scorable_id", "scorable_type"];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
