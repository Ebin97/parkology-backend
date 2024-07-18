<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Base
{
    public $translatable = ['name'];
    protected $fillable=['name','internal','document','language'];

    public function UserType()
    {
        return $this->hasMany(User::class, 'type_id');
    }
    public function QuizType()
    {
        return $this->hasMany(QuizType::class, 'type_id');
    }
}
