<?php

namespace App\Models;

class Notifiaction extends Base
{

    protected $fillable = ['notifiable_type', 'notifiable_id', 'title', 'user_id', 'role', 'type', 'status'];
    public $translatable = ['title'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
