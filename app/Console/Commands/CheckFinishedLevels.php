<?php

namespace App\Console\Commands;

use App\Helper\_GameHelper;
use App\Helper\_OneSignalHelper;
use App\Models\Notifiaction;
use App\Models\User;
use App\Models\UserQuiz;
use App\Services\Facades\FTheme;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckFinishedLevels extends Command
{
    protected $signature = 'command:levelsCheck';

    protected $description = 'Check users who finished the level more than 24 hours ago';

    public function handle()
    {
        Log::info('This is some useful information about the command that just ran.');
        // Fetch users who finished the level more than 24 hours ago
        $wheres = [];
        $yesterday = Carbon::yesterday()->toDateString();
        $users =  User::query()->whereNotExists(function ($query) use ($yesterday) {
            $query->select('user_id')
                ->from('user_quizzes')
                ->whereColumn('users.id', 'user_quizzes.user_id')
                ->whereDate('user_quizzes.created_at', '>=', $yesterday);
        })->get();
        // Output the users
        $list = [];
        $title = trans('translate._LevelAvailable');
        foreach ($users as $user) {
            if($user->fcm){
                $list[] = $user->fcm;
                //                $notification = Notifiaction::query()->create([
//                    'title' => [
//                        "en" => $title,
//                        "ar" => $title,
//                    ],
//                    'type' => 'challenge',
//                    'status' => 0,
//                    'user_id' => $user->id
//                ]);
            }
        }

        $s = _OneSignalHelper::SendOnSignalMessageForList($list, -1, "Parkology", null, $title, 'challenge', '', '', 'ios');
        _OneSignalHelper::SendOnSignalMessageForList($list, -1, "Parkology", null, $title, 'challenge', '', '', 'android');
    }
}
