<?php


namespace App\Helper;


class _RuleHelper
{
    const _Rule_Require = 'required';
    const _Rule_Email = 'email';
    const _Rule_Number = 'numeric';
    const _Rule_Date = 'date';
    const _Rule_Time = 'date_format:H:i';
    const _Rule_After_Time = 'date_format:H:i|after:';
    const _Rule_Min = 'min:';
    const _Rule_Max = 'max:';


    const _LOCKED = -1;
    const _SOLVED = 0;
    const _AVAILABLE = 1;
    const _LOST = 2;
}
