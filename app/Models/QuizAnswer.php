<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class QuizAnswer extends Base
{
    use SoftDeletes;
    public $translatable = ['title','slug'];

    protected $fillable = ['slug', 'title', 'correct', 'orders','quiz_id'];

    public function Quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function UserQuizzes()
    {
        return $this->hasMany(UserQuiz::class, 'answer_id');
    }

}
