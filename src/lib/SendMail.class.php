<?php

namespace lib;

class SendMail
{
    private string $emailFrom;

    private string $emailTo;
    
    private string $nameFrom;
    
    private string $nameTo;
    
    private string $replyTo;
    
    private string $subject;

    private string $message;
    
    private array $headers = [];
    
    private string $organization;

    private array $errArr = [];


    public function __construct(
        string $emailTo = 'koruri821@yahoo.co.jp',
    )
    {
        mb_language( 'Japanese' );
        mb_internal_encoding( 'UTF-8' );

        $this->emailFrom = 'eighthwonder_getthenack@yahoo.co.jp';
        $this->emailTo = $emailTo;
        $this->nameFrom = mb_encode_mimeheader('家計簿アプリ運営');
        $this->replyTo = mb_encode_mimeheader('家計簿アプリ運営');
    }

    public function send($token) : bool
    {
        if ($this->validateMail()) {
            $this->setSubject();
            $this->setMessage($token);
            $this->setHeaders();

            $res = mb_send_mail(
                $this->emailTo,
                $this->subject,
                $this->message,
                $this->headers,
            );

            if ($res) {
                return $res;
            } else {
                $this->errArr['send_failed'] = 'メッセージの送信に失敗しました。';
                return $res;
            }
        } else {
            return false;
        }
    }

    private function validateMail(): bool
    {
        $pattern = '/^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/';
        $flg = true;

        if (empty($this->emailFrom)) {
            $this->errArr['emailFrom_empty '] = '送信元メールアドレスが設定されていません';
            $flg = false;
        } elseif (! preg_match($pattern, $this->emailFrom)) {
            $this->errArr['emailFrom_invalid '] = '送信元メールアドレスの形式が不正です';
            $flg = false;
        }
        if (empty($this->emailTo)) {
            $this->errArr['emailFrom_empty '] = '宛先メールアドレスが設定されていません';
            $flg = false;   
        } elseif (! preg_match($pattern, $this->emailFrom)) {
            $this->errArr['emailFrom_invalid '] = '宛先メールアドレスの形式が不正です';
            $flg = false;
        }

        return $flg;
    }

    private function setHeaders() : void
    {
        //参考：PHPで日本語のメールを送信する時のおさらい
        //https://qiita.com/ka215/items/e5d21fe91a30fa968a2a
        $this->headers = [
            'MIME-Version' => '1.0',
            'Content-Transfer-Encoding' => '8bit',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Return-Path' => 'from@example.com',
            'From' => $this->nameFrom . ' <' . $this->emailFrom . '>',
            'Sender' => $this->nameFrom . ' <' . $this->emailFrom . '>',
            // 'To' => $this->nameTo . ' <' . $this->emailTo . '>',
            'Reply-To' => $this->replyTo,
            'Organization' => mb_encode_mimeheader('家計簿アプリ運営'),
            'X-Sender' => $this->emailFrom,
            'X-Mailer' => '',
            'X-Priority' => '3',
        ];
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

            以下のURLより仮登録時に設定したパスワードを入力し、ID登録の完了をお願いいたします。
            {$url}?register=true&token={$token}
            上記URLの有効期限は24時間となります。

            ※本メールにお心当たりがない場合は、メールを破棄してください。
            ※本メールは送信専用アドレスのため、返信いただいてもご回答いたしかねます。

            今後とも家計簿アプリをご愛顧いただけますよう、よろしくお願いいたします。
        MAIL;

        $message = mb_convert_encoding($message, 'UTF-8');
        $this->message = $message;
    }

    public function setEmailFrom($emailFrom) : void
    {
        $this->emailFrom = $emailFrom;
    }

    public function setEmailTo($emailTo) : void
    {
        $this->emailTo = $emailTo;
    }

    public function setNameFrom($nameFrom) : void
    {
        $this->nameFrom = $nameFrom;
    }

    public function setNameTo($nameTo) : void
    {
        $this->nameTo = $nameTo;
    }

    public function getErrArr()
    {
        return $this->errArr;
    }
}
