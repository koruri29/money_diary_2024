<?php

namespace lib;

use lib\common\PDODatabase;

class User
{
    const ADMIN = 99;

    const REGULAR_USER = 0;

    const DELETE_FLG_OFF = 0;

    const DELETE_FLG_ON = 1;

    private int $role;

    private int $user_id;

    private string $user_name;

    private string $email;

    private string $password;

    private int $delete_flg;

    private array $errArr = [];


    public function __construct(
        string $user_name,
        string $email,
        string $password,
        int $role = self::REGULAR_USER, 
        int $delete_flg = self::DELETE_FLG_OFF,
    )
    {
        $this->user_name = $user_name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->delete_flg = $delete_flg;
    }

    public function validateUser() : bool
    {
        $flg = true;

        if (empty($this->user_name)) {
            $this->errArr['username_empty'] = 'ユーザー名を入力してください。';
            $flg = false;
        } elseif (mb_strlen($this->user_name) > 50) {
            $this->errArr['username_too_long'] = 'ユーザー名は50文字以内で入力してください。';
            $flg = false;
        }


        if (empty($this->password)) {
            $this->errArr['password_empty'] = 'パスワードを入力してください。';
            $flg = false;
        }

        return $flg;
    }

    public static function getUserByEmail(PDODatabase $db, string $email) : User|bool
    {
        $db->resetClause();

        $table = ' users ';
        $column = ' id, user_name, email, password, role ';
        $where = ' email = ? ';
        $arr_val = [$email];

        $user_info = $db->select($table, $column, $where, $arr_val);

        if (empty($user_info)) return false;

        $user = new User(
            $user_info[0]['user_name'],
            $user_info[0]['email'],
            $user_info[0]['password'],
            $user_info[0]['role'],
        );
        $user->setUserId($user_info[0]['id']);

        return $user;
    }

    public static function getUserById(PDODatabase $db, int $user_id) : User|bool
    {
        $db->resetClause();
        
        $table = ' users ';
        $column = ' id, user_name, role, email, delete_flg ';
        $where = ' id = ? ';
        $arr_val = [$user_id];

        $user_info = $db->select($table, $column, $where, $arr_val);

        if (empty($user_info)) return false;

        $user = new User(
            $user_info[0]['user_name'],
            $user_info[0]['email'],
            '',
            $user_info[0]['role'],
        );
        $user->setUserId($user_info[0]['id']);
        $user->setDeleteFlg($user_info[0]['delete_flg']);

        return $user;
    }

    public static function doesEmailExist(PDODatabase $db, string $email) : bool
    {
        $db->resetClause();

        $table = 'users';
        $column = ' id ';
        $where = ' email = ? ';
        $arr_val = [$email];

        $user_info = $db->select($table, $column, $where, $arr_val);

        if (count($user_info) === 0) {
            return false;
        } else {
            return true;
        }
    }

    public static function getIds(PDODatabase $db) : array
    {
        $table = 'users';
        $column = ' id ';

        return $db->select($table, $column);

    }

    public function getRole() : int
    {
        return $this->role;
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
    
    public function setDeleteFlg(int $delete_flg) : string
    {
        return $this->delete_flg = $delete_flg;
    }
        
    public function getDeleteFlg() : string
    {
        return $this->delete_flg;
    }

    public function pushErrArr(array $err_arr) : void
    {
        $this->errArr = array_merge($this->errArr, $err_arr);
    }
    public function getErrArr()
    {
        return $this->errArr;
    }
}
