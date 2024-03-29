<?php

namespace lib;

require_once dirname(__FILE__) . '/common/Token.class.php';

use lib\common\Bootstrap;
use lib\common\Common;
use lib\common\PDODatabase;
use lib\common\Token;

class TmpUser
{
    private static array $err_arr = []; 

    public static function registerTmpUser(PDODatabase $db, string $email, string $token) : bool
    {
        if (! self::validateEmail($email)) return false;

        $expiration_time = 60 * 15;
        $expires = date('Y-m-d H:i:s', time() + $expiration_time);

        $table = 'tmp_users';
        $insertData = [
            'email' => $email,
            'token' => $token,
            'expires' => $expires,
        ];
        $res = $db->insert($table, $insertData);

        return $res;
    }


    private static function validateEmail(string $email) : bool
    {
        $flg = true;
        $pattern = Common::EMAIL_PATTERN;

        if (empty($email)) {
            self::$err_arr['red__email_empty'] = 'メールアドレスを入力してください。';
            $flg = false;
        } elseif (! preg_match($pattern, $email)) {
            self::$err_arr['red__email_invalid'] = '有効なメールアドレスを入力してください。';
            $flg = false;
        } elseif (mb_strlen($email) > 100) {
            self::$err_arr['red__email_too_long'] = 'メールアドレスは100文字以内で入力してください。';
            $flg = false;
        }

        return $flg;
    }

    public static function getTmpUser(PDODatabase $db, string $token) : array|bool
    {
        $table = ' tmp_users ';
        $column = ' id, email, token, expires ';
        $where = ' token = ? ';
        $arrVal = [$token];

        $res = $db->select($table, $column, $where, $arrVal);

        if (empty($res)) {
            return false;
        } else {
            return $res[0];
        }
    }

    public static function deleteTmpUser(PDODatabase $db, string $email) : bool
    {
        $table = 'tmp_users';

        $res = $db->delete($table, ['email' => $email]);

        return $res;
    }

    public static function getTmpUserIdByEmail(PDODatabase $db, string $email) : int
    {
        $table = ' tmp_users ';
        $column = ' id ';
        $where = ' email = ? ';
        $arr_val = [$email];

        $res =  $db->select($table, $column, $where, $arr_val);

        return $res[0]['id'];
    }

    public static function getErrArr() : array
    {
        return self::$err_arr;
    }
}
