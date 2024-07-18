<?php

namespace App\Models;

use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends Model
{
    use HasFactory, SoftDeletes,Mediable;

    protected $fillable = ['start_date', 'end_date', 'name', 'orders'];


}
