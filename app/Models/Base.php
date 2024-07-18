<?php


namespace App\Models;

use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Base extends Model
{
    use HasFactory, SoftDeletes, Mediable, HasTranslations;
}
