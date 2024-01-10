<?php

namespace lib;

use lib\common\PDODatabase;

class User
{
    private int $user_id;

    private string $user_name;

    private string $email;

    private string $password;

    private string $user_image;

    private array $errArr = [];


    public function __construct(
        string $user_name,
        string $email,
        string $password,
        string $user_image = '',
    )
    {
        $this->user_name = $user_name;
        $this->email = $email;
        $this->password = $password;
        $this->user_image = $user_image;

    }

    public function validateUser() : void
    {

        if (empty($this->user_name)) {
            $this->errArr['username_empty'] = 'ユーザー名を入力してください。';
        } elseif (mb_strlen($this->user_name) > 50) {
            $this->errArr['username_too_long'] = 'ユーザー名は50文字以内で入力してください。';
        }


        if (empty($this->password)) {
            $this->errArr['password_empty'] = 'パスワードを入力してください。';
        }
    }

    public static function getUserByEmail(PDODatabase $db, string $email) : ?User
    {
        $table = ' users ';
        $column = ' user_id, user_name, email, password, user_image ';
        $where = ' email = ?';
        $arr_val = [$email];

        $user_info = $db->select($table, $column, $where, $arr_val);

        if (empty($user_info)) return null;

        if (is_null($user_info[0]['user_image'])) $user_info[0]['user_image'] = '';
        $user = new User(
            $user_info[0]['user_name'],
            $user_info[0]['email'],
            $user_info[0]['password'],
            $user_info[0]['user_image'],
        );
        $user->setUserId($user_info[0]['user_id']);

        return $user;
    }

    public function setUserId($user_id) : void
    {
        $this->user_id = $user_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getUserName() : string
    {
        return $this->user_name;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function getUserImage() : string
    {
        return $this->user_image;
    }

    public function getErrArr()
    {
        return $this->errArr;
    }
}
