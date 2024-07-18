<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizType extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['quiz_id', 'type_id','level'];

    public function Quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function Type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
