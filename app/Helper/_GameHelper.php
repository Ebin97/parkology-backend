<?php

namespace App\Helper;

class _GameHelper
{

    const _VIDEO_SCORE = 5;
    const _QUIZ_FIRST_ATTEMPT = 5;
    const _QUIZ_SECOND_ATTEMPT = 3;
    const _QUIZ_THIRD_ATTEMPT = 1;
    const _QUIZ_FAIL = 0;

    public static function _CalculateScore($attempts): int
    {
        switch ($attempts) {
            case 3:
                return self::_QUIZ_THIRD_ATTEMPT;
            case 2:
                return self::_QUIZ_SECOND_ATTEMPT;
            case 1:
                return self::_QUIZ_FIRST_ATTEMPT;
            default:
                return self::_QUIZ_FAIL;
        }
    }

    public static function _CalculateLife($attempts): int
    {
        switch ($attempts) {
            case 3:
                return 0;
            case 2:
                return 1;
            case 1:
                return 2;
            default:
                return 3;
        }
    }

    public static function _VideoScore(): int
    {
        return self::_VIDEO_SCORE;
    }

}
