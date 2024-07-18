<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = ['email', 'full_name', 'user_id', 'message_text'];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
