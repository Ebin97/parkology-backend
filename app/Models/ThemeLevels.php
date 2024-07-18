<?php

namespace App\Models;

use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThemeLevels extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = ['level', 'orders', 'active', 'theme_id', 'quiz_id'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }

    public function userQuiz()
    {
        return $this->hasMany(UserQuiz::class, 'level_id');
    }
}
