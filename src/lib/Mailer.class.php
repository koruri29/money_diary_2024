<?php

namespace lib;

require_once __DIR__ . '/common/Bootstrap.class.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use lib\common\Bootstrap;
use lib\common\Common;


class Mailer
{
    private PHPMailer $mail;

    private string $emailFrom;

    private string $emailTo;

    private string $subject;

    private string $message;

    private array $errArr = [];


    public function __construct(PHPMailer $mail,)
    {
        mb_language( 'Japanese' );
        mb_internal_encoding( 'UTF-8' );

        $this->mail = $mail;
    }

    public function setProperties( string $emailTo, string $token): void
    {
        $this->emailTo = $emailTo;
        $this->setSubject();
        $this->setMessage($token);

        try {
            //Charset, encoding
            $mail->CharSet  = 'iso-2022-jp';
            $mail->Encoding = '7bit';

            //Server settings
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $this->mail->isSMTP();                                            //Send using SMTP
            $this->mail->Host       = Bootstrap::SMTP_SERVER;                     //Set the SMTP server to send through
            $this->mail->SMTPAuth   = Bootstrap::SMTP_AUTH;                                   //Enable SMTP authentication
            $this->mail->Username   = Bootstrap::EMAIL_USER_NAME;                     //SMTP username
            $this->mail->Password   = Bootstrap::EMAIL_PASSWORD;                               //SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $this->mail->Port       = Bootstrap::SMTP_PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $this->mail->setFrom(Bootstrap::EMAIL_FROM, '家計簿アプリ運営');
            $this->mail->addAddress($this->emailTo);     //Add a recipient

            //Content
            $this->mail->isHTML(false);                                  //Set email format to HTML
            $this->mail->Subject = $this->subject;
            $this->mail->Body    = $this->message;

        } catch (Exception $e) {
        }
    }

    public function send(): bool
    {
        if ($this->validateEmail()) {
            try {
                $res = $this->mail->send();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            }

            return $res;
        } else {
            return false;
        }
    }

    private function validateEmail(): bool
    {
        $pattern = Common::EMAIL_PATTERN;
        $flg = true;

        if (empty($this->emailTo)) {
            $this->errArr['emailTo_empty '] = '宛先メールアドレスが設定されていません';
            $flg = false;
        } elseif (! preg_match($pattern, $this->emailTo)) {
            $this->errArr['emailTo_invalid '] = '宛先メールアドレスの形式が不正です';
            $flg = false;
        }

        return $flg;
    }

    private function setSubject() : void
    {
        $this->subject = '家計簿アプリ会員登録';
    }

    private function setMessage($token) : void
    {
        $url = 'http://localhost/DT_2024/money_diary_2024/src/register.php';
        $message = <<<MAIL
            家計簿アプリへの登録ありがとうございます。
            仮登録を受付いたしました。

            以下のURLより、登録の完了をお願いいたします。
            {$url}?register=true&token={$token}
            上記URLの有効期限は24時間となります。

            ※本メールにお心当たりがない場合は、メールを破棄してください。
            ※本メールは送信専用アドレスのため、返信いただいてもご回答いたしかねます。

            今後とも家計簿アプリをご愛顧いただけますよう、よろしくお願いいたします。
        MAIL;

        $message = mb_convert_encoding($message, 'UTF-8');
        $this->message = $message;
    }

    public function getErrArr()
    {
        return $this->errArr;
    }
}
