<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speciality extends Base
{
    public $translatable = ['name'];
    protected $fillable=['name'];

    public function User()
    {
        return $this->hasMany(User::class, 'speciality_id');
    }
}
