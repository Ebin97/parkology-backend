<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductQuiz extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quiz_id'];


    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function products()
    {
        return $this->belongsTo(ProductKnowledge::class, 'product_id');
    }

}
