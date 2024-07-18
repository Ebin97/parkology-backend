<?php

namespace App\Models;

use App\Traits\Mediable;
use App\Traits\Notifiable;
use App\Traits\Scorable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductKnowledge extends Model
{
    use HasFactory, SoftDeletes, Mediable, Scorable, Notifiable;
    use HasTranslations;

    public $translatable = ['title','description'];

    protected $fillable = ['title', 'description', 'active'];
}
