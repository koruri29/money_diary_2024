<?php

namespace lib;

use lib\common\PDODatabase;
use lib\User;

class CSV {
    private $db = null;
    private $spl = null;
    private array $err_arr = [];

    /**
     * コンストラクタ
     * 
     * @param PDODatabase $db
     * @param string $csv_file $_FILES['name']を渡す
     */
    public function __construct(PDODatabase $db, array $csv_file)
    {
        if ($this->validateCSV($csv_file)) {
            throw new \Exception('ファイルの読み込みに失敗しました。エラー：' . implode(',', $this->err_arr));
        }

        $this->db = $db;
        $this->spl = new \SplFileObject($csv_file['tmp_name']);
        $this->spl->setFlags(\SplFileObject::READ_CSV);
    }
    /**
     * CSVでユーザーを一括登録する
     * CSV先頭行->user_name,email,password
    */
    public function registerUser() : bool
    {
        $table = ' users ';

        try {

            $this->db->dbh->beginTransaction();

            foreach ($this->spl as $line) {
                if ($line[0] === null) continue;
                $user_name = $line[0];
                $email = $line[1];
                $password = $line[2];
                
                if (strpos($user_name, '"') !== false) $user_name = str_replace('"', '', $user_name);// 1行目の0番目要素に””がついてしまうため、回避
                if (User::getUserByEmail($this->db, $email) !== false) continue;

                $password = password_hash($password, PASSWORD_DEFAULT);
                
                $insert_arr = [
                    'user_name' => $user_name,
                    'email' => $email,
                    'password' => $password,
                ];

                $this->db->insert($table, $insert_arr);
            }

            $this->db->dbh->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->dbh->rollBack();
            $this->err_arr['red__insert_csv_failed'] = '一括登録に失敗しました。';
            return false;
        }
    }

    private function validateCSV($csv_file) : bool
    {        $flg = true;

        if ($csv_file['error'] === 0 && $csv_file['size'] !== 0) {
            if ($csv_file['size'] > 1048576) {
                $this->err_arr['red__size_too_large'] = 'アップロードできる画像のサイズは、1MBまでです';
                $flg = false;
            }
            if (preg_match('/^text\/css$/', mime_content_type($csv_file['tmp_name'])) === 0) {
                $this->err_arr['red__mime_invalid'] =  'アップロードできる画像の形式は、CSV形式だけです';
                $flg = false;
            }
        }
        return $flg;
    }
    public function getErrArr()
    {
        return $this->err_arr;
    }
}
