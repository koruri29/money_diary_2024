<?php

namespace lib;

require_once dirname(__FILE__) . '/common/Token.class.php';

use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Token;

class TmpUser
{
    private $db;

    private int $tmp_user_id;

    private string $email;

    private string $token;

    private string $expires;

    private array $err_arr = [];

    private array $msg_arr = [];


    public function __construct(PDODatabase $db)
    {
        $this->db = $db;
    }

    public function registerTmpUser(string $email, string $token) : bool
    {
        $this->validateEmail($email);
        if (count($this->err_arr) > 0) return false;

        $this->setProperties($email, $token);

        $table = 'tmp_users';
        $insertData = [
            'email' => $this->email,
            'token' => $this->token,
            'expires' => $this->expires,
        ];
        $res = $this->db->insert($table, $insertData);

        if ($res) {
            return $res;
        } else {
            $this->msg_arr['red_insert_failed'] = '仮登録に失敗しました。';
            return $res;
        }
    }

    private function setProperties(string $email, string $token) : void
    {
        $expiration_time = 60 * 15;

        $this->email = $email;
        $this->token = $token;
        $this->expires = date('Y-m-d H:i:s', time() + $expiration_time);
    }

    public function validateEmail(string $email) : void
    {
        $pattern = Common::EMAIL_PATTERN;

        if (empty($email)) {
            $this->err_arr['email_empty'] = 'メールアドレスを入力してください。';
        } elseif (! preg_match($pattern, $email)) {
            $this->err_arr['email_invalid'] = '有効なメールアドレスを入力してください。';
        } elseif (mb_strlen($email) > 100) {
            $this->err_arr['email_too_long'] = 'メールアドレスは100文字以内で入力してください。';
        }
    }

    public function getTmpUser(string $token) : array
    {
        $table = ' tmp_users ';
        $column = ' tmp_user_id, email, token, expires ';
        $where = ' token = ? ';
        $arrVal = [$token];

        $res = $this->db->select($table, $column, $where, $arrVal);
        return $res[0];

    }

    public function getErrArr()
    {
        return $this->err_arr;
    }
}
