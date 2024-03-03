<?php

namespace lib;

use lib\common\PDODatabase;
use lib\Category;
use lib\User;
use lib\Wallet;

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
        if (! $this->validateCSV($csv_file)) {
            throw new \Exception('ファイルの読み込みに失敗しました。エラー：' . implode(',', $this->err_arr));
        }

        $this->db = $db;
        $this->spl = new \SplFileObject($csv_file['tmp_name']);
        $this->spl->setFlags(\SplFileObject::READ_CSV);
    }

    /**
     * CSVでユーザーを一括登録する
    */
    public function registerUser() : void
    {
        $this->db->resetClause();

        $table = ' users ';

        foreach ($this->spl as $line) {
            if ($line[0] === null) continue;

            $user_name = $line[0];
            $email = $line[1];
            $password = $line[2];

            if (strpos($user_name, '"') !== false) $user_name = str_replace('"', '', $user_name);// 1行目の0番目要素に””がついてしまうため、回避
            if (User::doesEmailExist($this->db, $email)) continue;

            $password = password_hash($password, PASSWORD_DEFAULT);

            $insert_arr = [
                'user_name' => $user_name,
                'email' => $email,
                'password' => $password,
            ];

            $this->db->insert($table, $insert_arr);

            //入出金カテゴリなどの初期化処理
            $user_id = $this->db->getLastId();
            Category::initCategories($this->db, $user_id);
            Wallet::initWallets($this->db, $user_id);
        }
    }

    private function validateCSV($csv_file) : bool
    {
        $flg = true;

        if ($csv_file['error'] === 0 && $csv_file['size'] !== 0) {
            if ($csv_file['size'] > 1048576) {
                $this->err_arr['red__size_too_large'] = 'アップロードできるサイズは、1MBまでです';
                $flg = false;
            }
            if (preg_match('/^text\/csv$/', mime_content_type($csv_file['tmp_name'])) === 0) {
                $this->err_arr['red__mime_invalid'] =  'アップロードできる形式は、CSV形式だけです';
                $flg = false;
            }
        }
        return $flg;
    }

    /**
     * ダミーデータ挿入用
     */
    public static function insertDummyEvents(PDODatabase $db, array $user_ids) : void
    {
        $db->resetClause();

        foreach ($user_ids as $user_id) {
            $user = User::getUserById($db, $user_id['id']);
            if ($user->getRole() === USER::ADMIN) continue;

            //ユーザーごとのカテゴリを取得
            $table = ' categories ';
            $column = ' id ';
            $where = ' user_id = ? ';
            $arr_val = [$user_id['id']];
            $db->setLimitOff(2, 0);

            $categories = $db->select($table, $column, $where, $arr_val);

            //財産カテゴリを取得
            $db->resetClause();
            $table = ' wallets ';
            $column = ' id ';
            $db->setLimitOff(2, 0);

            $wallets = $db->select($table, $column, $where, $arr_val);

            $date = date('Y-m-d');

            //入出金登録
            $table = ' money_events ';
            $insertData = [
                'user_id' => $user_id['id'],
                'category_id' => $categories[0]['id'],
                'wallet_id' => $wallets[0]['id'],
                'option' => 0,
                'amount' => 1500,
                'date' => $date . ' 00:00:00',
                'other' => 'ダミーデータです。',
            ];
            $db->insert($table, $insertData);// 2レコード分追加
            $db->insert($table, $insertData);
        }
    }

    public function getErrArr() : array
    {
        return $this->err_arr;
    }
}
