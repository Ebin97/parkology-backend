<?php

namespace App\Models;

use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Settings extends Model
{
    use HasFactory, HasTranslations, Mediable;
    public $translatable = [];
    protected $fillable = ["api_status","quizzes_per_day"];

}
