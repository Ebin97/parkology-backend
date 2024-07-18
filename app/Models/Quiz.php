<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Base
{
    public $translatable = ['title','slug'];

    protected $fillable=['slug','title','level','active','bonus','start_time','end_time'];

    public function Answers()
    {
        return $this->hasMany(QuizAnswer::class, 'quiz_id');
    }
    public function QuizType()
    {
        return $this->hasMany(QuizType::class, 'quiz_id');
    }

    public function ThemeLevel()
    {
        return $this->hasMany(ThemeLevels::class, 'quiz_id');
    }

    public function UserQuizzes()
    {
        return $this->hasMany(UserQuiz::class, 'quiz_id');
    }

}
