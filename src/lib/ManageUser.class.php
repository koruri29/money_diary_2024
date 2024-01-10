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
        $password = $this->user->getPassword();
        $password = password_hash($password, PASSWORD_DEFAULT);

        $table = 'users';
        $insertData = [
            'user_name' => $this->user->getUserName(),
            'email' => $this->user->getEmail(),
            'password' => $password,
            'user_image' => $this->user->getUserImage(),
        ];

        $res = $this->db->insert($table, $insertData);

        return $res;
    }

    public function updateUser(int $userId) : bool
    {
        $this->user->validateUser();
        if (count($this->user->getErrArr()) > 0) {
            return false;
        }
        
        $table = 'users';
        $insertData = [
            'user_name' => $this->user->getUserName(),
            'email' => $this->user->getEmail(),
            'user_image' => $this->user->getUserImage(),
        ];
        $where = ' user_id = ? ';
        $arrWhereVal = [$userId];

        $res = $this->db->update($table, $insertData, $where, $arrWhereVal);

       if ($res) {
        return true;
       } else {
        return false;
       }
    }

    public function deleteUser(int $userId) : bool
    {
        $table = 'users';
        $insertData = [
            'user_name' => $this->user->getUserName(),
            'email' => $this->user->getEmail(),
            'user_image' => $this->user->getUserImage(),
        ];
        $where = ' user_id = ? ';
        $arrWhereVal = [$userId];

        $res = $this->db->update($table, $insertData, $where, $arrWhereVal);

       if ($res) {
        return true;
       } else {
        return false;
       }
    }


    public static function getAllUsers(PDODatabase $db) : array
    {
        $table = ' users ';
        $column = ' user_id, user_name, email, password, user_image ';
        $users = $db->select($table, $column);

        return $users;
    }

    // public function getErrArr()
    // {
    //     return $this->err_arr;
    // }
}
