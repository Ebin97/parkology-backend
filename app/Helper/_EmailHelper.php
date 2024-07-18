<?php


namespace App\Helper;


use App\Models\UserToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class _EmailHelper
{
    public function __construct()
    {

    }

    /**
     * @return PHPMailer
     */
    public static function getPHPMailer(): PHPMailer
    {
        $mail = new PHPMailer();
        // SMTP configurations
        $mail->isSMTP();
        $mail->Host = 'smtp.dreamhost.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@parkology-egy.com';
        $mail->Password = 'xnCNzMj92^LT';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        return $mail;
    }

    public static function generateToken($user, $expired_at)
    {
        $check = UserToken::query()->where([
            'user_id' => $user->id,
        ])->where('expired_at', '>', Carbon::now())->first();
        if ($check) {
            return $check->token;
        }
        $token = UserToken::query()->create([
            'token' => Str::slug(Hash::make($user->email)),
            'user_id' => $user->id,
            'expired_at' => $expired_at
        ]);
        return $token->token;
    }

    public function sendVerification($user)
    {
        $token = $this->generateToken($user, Carbon::now()->addHours(6));
        $data = ["token" => $token];
        return $this->sendEmail($user, $data, 'email');
    }

    public function sendResetPassword($user)
    {
        $token = $this->generateToken($user, Carbon::now()->addHours(1));
        $data = ["token" => $token];
        return $this->sendEmail($user, $data, 'reset-email');

    }

    public static function sendEmail($user, $data, $view)
    {
        try {
            $mail = self::getPHPMailer();
            $mail->setFrom('info@parkology-egy.com', 'Parkology');
            $mail->Sender = "info@parkology-egy.com";
            $mail->ContentType = "text/html;charset=UTF-8\r\n";
            $mail->Priority = 3;
            $mail->addCustomHeader("MIME-Version: 1.0\r\n");
            $mail->addCustomHeader("X-Mailer: PHP'" . phpversion() . "'\r\n");
            $mail->addAddress($user->email, $user->email);
            $mail->addReplyTo('info@parkology-egy.com', 'Parkology');
            $mail->Subject = 'Parkology';

            $mail->isHTML(true);
            // Email body content
            $mail->Body = view($view, $data)->render();
            // Send email
            if ($mail->send()) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return false;
    }

    public static function sendOtpEmail($email, $data, $view)
    {
        try {
            $mail = self::getPHPMailer();
            $mail->setFrom('info@parkology-egy.com', 'Parkology');
            $mail->Sender = "info@parkology-egy.com";
            $mail->ContentType = "text/html;charset=UTF-8\r\n";
            $mail->Priority = 3;
            $mail->addCustomHeader("MIME-Version: 1.0\r\n");
            $mail->addCustomHeader("X-Mailer: PHP'" . phpversion() . "'\r\n");
            $mail->addAddress($email);
            $mail->addReplyTo('info@parkology-egy.com', 'Parkology');
            $mail->Subject = 'Parkology';

            $mail->isHTML();
            // Email body content
            $mail->Body = view($view, $data)->render();
            // Send email
            if ($mail->send()) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return false;
    }


    public static function sendEmailToParkology($user, $data, $view)
    {
        try {
            $mail = self::getPHPMailer();
            $mail->setFrom("info@parkology-egy.com", 'Parkology Redeem');
            $mail->Sender = "info@parkology-egy.com";
            $mail->ContentType = "text/html;charset=UTF-8\r\n";
            $mail->Priority = 3;
            $mail->addCustomHeader("MIME-Version: 1.0\r\n");
            $mail->addCustomHeader("X-Mailer: PHP'" . phpversion() . "'\r\n");
            $mail->addAddress('apple@parkvillepharma.com', 'Parkology');
            $mail->addReplyTo($user->email, $user->email);
            $mail->Subject = 'Parkology Redeem';

            $mail->isHTML(true);
            // Email body content
            $mail->Body = view($view, $data)->render();
            // Send email
            if ($mail->send()) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return false;
    }


    public static function sendSyndicateEmailToParkology($user, $data, $view, $attachment = [])
    {
        try {
            $mail = self::getPHPMailer();
            $mail->setFrom("info@parkology-egy.com", 'Parkology - Syndicate verify');
            $mail->Sender = "info@parkology-egy.com";
            $mail->ContentType = "text/html;charset=UTF-8\r\n";
            $mail->Priority = 3;
            $mail->addCustomHeader("MIME-Version: 1.0\r\n");
            $mail->addCustomHeader("X-Mailer: PHP'" . phpversion() . "'\r\n");
//            $mail->addAddress('shawn.f@medcon-me.com', 'Parkology');
            $mail->addAddress('ali.shamia@medcon-me.com', 'Parkology');
            $mail->addAddress('shamiaali7@gmail.com', 'Parkology');
            $mail->addReplyTo($user->email, $user->email);
            $mail->Subject = 'Parkology - Syndicate verify';

            $mail->isHTML(true);
            // Email body content
            $mail->Body = view($view, $data)->render();
            Log::error($attachment);
            foreach ($attachment as $item) {
                $mail->addAttachment($item);
            }
            // Send email
            if ($mail->send()) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return false;
    }

    public static function sendReceiptEmailToParkology($user, $data, $view, $attachment = [])
    {
        try {
            $mail = self::getPHPMailer();
            $mail->setFrom("info@parkology-egy.com", 'Parkology - Receipt');
            $mail->Sender = "info@parkology-egy.com";
            $mail->ContentType = "text/html;charset=UTF-8\r\n";
            $mail->Priority = 3;
            $mail->addCustomHeader("MIME-Version: 1.0\r\n");
            $mail->addCustomHeader("X-Mailer: PHP'" . phpversion() . "'\r\n");
//            $mail->addAddress('shawn.f@medcon-me.com', 'Parkology');
            $mail->addAddress('ali.shamia@medcon-me.com', 'Parkology');
            $mail->addAddress('shamiaali7@gmail.com', 'Parkology');
            $mail->addReplyTo($user->email, $user->email);
            $mail->Subject = 'Parkology - Receipt';

            $mail->isHTML(true);
            // Email body content
            $mail->Body = view($view, $data)->render();
            foreach ($attachment as $item) {
                $mail->addAttachment($item);
            }
            // Send email
            if ($mail->send()) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return false;
    }

    public static function sendReceiptStatusEmailToUser($user, $data, $view, $attachment = [])
    {
        try {
            $mail = self::getPHPMailer();
            $mail->setFrom("info@parkology-egy.com", 'Parkology - Receipt Status');
            $mail->Sender = "info@parkology-egy.com";
            $mail->ContentType = "text/html;charset=UTF-8\r\n";
            $mail->Priority = 3;
            $mail->addCustomHeader("MIME-Version: 1.0\r\n");
            $mail->addCustomHeader("X-Mailer: PHP'" . phpversion() . "'\r\n");
//            $mail->addAddress('shawn.f@medcon-me.com', 'Parkology');
            $mail->addAddress($user->email);
            $mail->addReplyTo('no-reply@parkology-egy.com');
            $mail->Subject = 'Parkology - Receipt Status';

            $mail->isHTML(true);
            // Email body content
            $mail->Body = view($view, $data)->render();
            foreach ($attachment as $item) {
                $mail->addAttachment($item);
            }
            // Send email
            if ($mail->send()) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return false;
    }


}
