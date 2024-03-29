<?php

namespace lib;

use lib\common\PDODatabase;
use lib\User;

class ManageUser
{
    private $db;

    private  User $user;

    // private array $err_arr = [];

    public function __construct(PDODatabase $db, User $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    public function registerUser(): bool
    {
        //バリデーション
        if (! $this->user->validateUserRegister()) return false;

        $password = $this->user->getPassword();
        $password = password_hash($password, PASSWORD_DEFAULT);

        $table = 'users';
        $insertData = [
            'user_name' => $this->user->getUserName(),
            'email' => $this->user->getEmail(),
            'password' => $password,
        ];

        $res = $this->db->insert($table, $insertData);

        return $res;
    }

    public function updateUser() : bool
    {
        // if ($this->user->getRole() !== User::ADMIN && $this->user->getUserId() !== $userId) return false;

        $existing_user = User::getUserByEmail($this->db, $this->user->getEmail());
        if (User::doesEmailExist($this->db, $this->user->getEmail()) &&
        $existing_user->getUserId() !== $this->user->getUserId()) {
            echo '!!!';
            throw new \Exception('すでに登録されているメールアドレスです。');
        }

        if (empty($this->user->getEmail())) {
            throw new \Exception('メールアドレスが入力されていません。');
        }

        if (! $this->user->validateUserName()) {
            throw new \Exception('ユーザー名が正しくありません。');
        }

        $table = ' users ';
        $insertData = [
            'user_name' => $this->user->getUserName(),
            'email' => $this->user->getEmail(),
            'role' => $this->user->getRole(),
            'delete_flg' => $this->user->getDeleteFlg(),
        ];
        $where = ' id = ? ';
        $arrWhereVal = [$this->user->getUserId()];

        $res = $this->db->update($table, $insertData, $where, $arrWhereVal);

        return $res;
    }

    public function deleteUser(int $userId) : bool
    {
        // if ($this->user->getRole() !== User::ADMIN && $this->user->getUserId() !== $userId) return false;

        $table = 'users';
        $insertData = ['delete_flg' => 1];
        $where = ' id = ? ';
        $arrWhereVal = [$userId];

        $res = $this->db->update($table, $insertData, $where, $arrWhereVal);

        return $res;
    }

    public static function getAllUsers(PDODatabase $db) : array
    {
        $table = ' users ';
        $column = ' id, user_name, role, email, password, delete_flg ';

        $db->setLimitOff();
        $users = $db->select($table, $column);

        return $users;
    }


    public static function getAllUsersForCSV(PDODatabase $db) : array
    {
        $table = ' users ';
        $column = ' id, user_name, role, email, delete_flg, created_at, updated_at ';
        $users = $db->select($table, $column);

        return $users;
    }

    public function getUser() : User
    {
        return $this->user;
    }
}
