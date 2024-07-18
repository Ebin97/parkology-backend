<?php

namespace App\Traits;

use App\Models\Notifiaction;

trait Notifiable
{
    public function users()
    {
        return $this->morphMany(Notifiaction::class, 'notifiable')->where(['status'=> true,'type'=>'public'])->select(['id','title','user_id','role','type','status']);
    }
    public function roles()
    {
        return $this->morphMany(Notifiaction::class, 'notifiable')->where(['status'=> true,'type'=>'role'])->select(['id','title','user_id','role','type','status']);
    }
    public function user()
    {
        return $this->morphMany(Notifiaction::class, 'notifiable')->where(['status'=> true,'type'=>'private']);
    }
    public function challenge()
    {
        return $this->morphMany(Notifiaction::class, 'notifiable')->where(['status'=> true,'type'=>'challenge']);
    }
    public function receipt()
    {
        return $this->morphMany(Notifiaction::class, 'notifiable')->where(['status'=> true,'type'=>'sales']);
    }
}
