<?php

namespace App\Models;

use App\Traits\Scorable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuiz extends Model
{
    use HasFactory,Scorable;

    protected $fillable = ["attempts", "quiz_id",  "user_id", "answer_id", "level_id"];

    public function Answer()
    {
        return $this->belongsTo(QuizAnswer::class, 'answer_id');
    }

    public function Quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Level()
    {
        return $this->belongsTo(ThemeLevels::class, 'level_id');
    }

}
