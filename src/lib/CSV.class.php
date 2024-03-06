<?php

namespace lib;

use Google\Service\FirebaseRules\FunctionCall;
use lib\common\PDODatabase;
use lib\Category;
use lib\User;
use lib\Wallet;
use Twig\Node\Expression\FunctionExpression;

class CSV {

    const OUTPUT_USERS = 0;

    const OUTPUT_EVENTS = 1;

    private $db = null;

    private $spl = null;

    private static array $err_arr = [];

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
    public function registerUser() : int
    {
        $this->db->resetClause();

        $table = ' users ';
        $i = 0;

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

            $i++;
        }
        return $i;
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

    private static function getAllUsersForOutput(PDODatabase $db) : array
    {
        $table = ' users ';
        $column = ' id, user_name, role, email, delete_flg, created_at, updated_at ';
        $users = $db->select($table, $column);

        return $users;
    }

    public function downloadCSV(string $filepath) : bool
    {
        //【PHP】正しいダウンロード処理の書き方(Qiita)
        //https://qiita.com/fallout/items/3682e529d189693109eb

        //-- ファイルが読めない時はエラー(もっときちんと書いた方が良いが今回は割愛)
        if (!is_readable($pPath)) {

        }

        $mimeType = 'text/csv';

        //-- 適切なMIMEタイプが得られない時は、未知のファイルを示すapplication/octet-streamとする
        if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
            $mimeType = 'application/octet-stream';
        }

        //-- Content-Type
        header('Content-Type: ' . $mimeType);

        //-- ウェブブラウザが独自にMIMEタイプを判断する処理を抑止する
        header('X-Content-Type-Options: nosniff');

        //-- ダウンロードファイルのサイズ
        header('Content-Length: ' . filesize($pPath));

        //-- ダウンロード時のファイル名
        header('Content-Disposition: attachment; filename="' . basename($pPath) . '"');

        //-- keep-aliveを無効にする
        header('Connection: close');

        //-- readfile()の前に出力バッファリングを無効化する ※詳細は後述
        while (ob_get_level()) { ob_end_clean(); }

        //-- 出力
        readfile($pPath);

        //-- 最後に終了させるのを忘れない
        exit;
    }

    /**
     *
     */
    public static function createCSV(array $arr, int $mode, int $user_id = null) : string | false
    {
        //ファイル名生成
        $file_prefix = '';
        $top_arr = [];

        switch ($mode) {
            case self::OUTPUT_USERS:
                $file_prefix = 'users';
                $top_arr = ['"ID"', '"ユーザー名"', '"管理者フラグ"', '"メールアドレス"', '"削除フラグ"', '"登録日"', '"更新日"'];
            case self::OUTPUT_EVENTS:
                $file_prefix = 'events_user-id' . $user_id;
                $top_arr = [];
            default:
                self::$err_arr['mode_invalid'] = '不正なパラメータです。';
                return false;
        }
        array_unshift($arr, $top_arr);

        $filename = date('Y-m-d-H-i-s') . '_' . $file_prefix;
        $file_dir = '../../csv/';
        $filepath = self::nameFileWithoutCollision($file_dir, $filename);

        //ファイル作成
        $fp = fopen($filepath, 'w');
        foreach ($arr as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);

    }

    private static function nameFileWithoutCollision(string $file_dir, string $filename) : string
    {
        $i = 2;
        if (file_exists($file_dir . $filename . 'csv')) {
            while (file_exists($file_dir . $filename . '_' . $i . 'csv')) {
                $i++;
            }
            return $file_dir . $filename . '_' . $i . '.csv';
        }
        return $file_dir . $filename . '.csv';
    }

    public function getErrArr() : array
    {
        return $this->err_arr;
    }
}
