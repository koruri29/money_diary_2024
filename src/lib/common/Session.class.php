<?php

namespace lib\common;

use lib\common\Common;
use lib\common\PDODatabase;
use lib\User;

class Session
{
    private $db = null;
    
    private array $err_arr = [];

    public function __construct(PDODatabase $db)
    {
        session_start();
        session_regenerate_id(true);

        $this->db = $db;
    }

    public function setUserInfo(User $user) : void
    {
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['user_name'] = $user->getUserName();
    }

    public static function deleteSession() : void
    {
        $_SESSION = [];
        session_destroy();
    }
    
    public function checkToken() : bool
    {
        if ($_SESSION['token'] === $_POST['token']) {
            return true;
        } else {
            return false;
        }
    }


    public function checkTmpUserToken($token) : bool
    {
        $table = ' tmp_users ';
        $column = ' tmp_user_id, email, token, expires ';
        $where = ' token = ? ';
        $arrVal = [$token];

        $res = $this->db->select($table, $column, $where, $arrVal);

        if ($res === false || strtotime($res['expires']) < time()) {
            return false;
        }

        return true;
    }

    public function checkAutoLoginToken() : bool
    {

    }



    public function isLogin() : bool
    {

    }

    /**
     * ログイン認証
     * 
     * @param User $user
     * @param string $email $_POST['email']
     * @param string $password $_POST['password']
     * @return bool|User ログイン成功時、Userクラスを返す。失敗時、falseを返す。
     */
    public function checkLogin(string $email, string $password, bool $admin_flg = false) : bool | User
    {
        if (empty($email)) {
            $this->err_arr['red__email_empty'] = 'メールアドレスを入力してください。';
        } else if (! preg_match(Common::EMAIL_PATTERN, $email)) {
            $this->err_arr['red__password_invalid'] = '有効なメールアドレスを入力してください。';
        }
        if (empty($password)) $this->err_arr['red__password_empty'] = 'パスワードを入力してください。';

        if (count($this->err_arr) > 0) return false;

        $user = User::getUserByEmail($this->db, $email);

        if (! $user) {
            $this->err_arr['red__login_failed'] = 'ユーザーIDかパスワードが間違っています。';
            return false;
        }
        
        if (! password_verify($password, $user->getPassword())) {
            $this->err_arr['red__login_failed'] = 'ユーザーIDかパスワードが間違っています。';
            return false;
        }

        if ($admin_flg && $user->getRole() !== User::ADMIN) {
            $this->err_arr['red__not admin'] = '管理者権限のあるユーザーでログインしてください。';
            return false;
        }

        return $user;
    }

    public function isSessionInEffect() : bool
    {

    }

    public function setAutoLogin()
    {

    }

    private function setTimeout()
    {

    }

    public function getErrArr() : array
    {
        return $this->err_arr;
    }
}
