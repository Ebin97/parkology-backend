<?php

namespace App\Traits;

use App\Models\UserScore;

trait Scorable
{
    public function totalScore()
    {
        return $this->morphMany(UserScore::class, 'scorable')->where('status', true)->select(['score', 'status', 'user_id', 'type']);
    }

    public function levelScore()
    {
        return $this->morphMany(UserScore::class, 'scorable')->where(['type' => 'quiz', 'status' => true])->select(['score', 'status', 'user_id', 'type']);
    }

    public function productScore()
    {
        return $this->morphMany(UserScore::class, 'scorable')->where(['type' => 'product', 'status' => true])->select(['score', 'status', 'user_id', 'type']);
    }

    public function receiptScore()
    {
        return $this->morphMany(UserScore::class, 'scorable')->where(['type' => 'receipt', 'status' => true])->select(['score', 'status', 'user_id', 'type']);
    }

    public function receiptPoint()
    {
        return $this->morphMany(UserScore::class, 'scorable')->where(['type' => 'receipt']);
    }

}
