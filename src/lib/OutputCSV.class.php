<?php

namespace lib;

use lib\common\Bootstrap;

class OutputCSV
{
    const OUTPUT_USERS = 0;

    const OUTPUT_EVENTS = 1;

    private $db = null;

    private array $err_arr = [];

    public function downloadCSV(string $filepath) : bool
    {
        //【PHP】正しいダウンロード処理の書き方(Qiita)
        //https://qiita.com/fallout/items/3682e529d189693109eb

        //-- ファイルが読めない時はエラー
        if (!is_readable($filepath)) {
            $this->err_arr['cannot_read_csv'] = 'CSVファイルが読み込めませんでした。';
            return false;
        }

        $mimeType = 'text/csv';

        //-- Content-Type
        header('Content-Type: ' . $mimeType);

        //-- ウェブブラウザが独自にMIMEタイプを判断する処理を抑止する
        header('X-Content-Type-Options: nosniff');

        //-- ダウンロードファイルのサイズ
        header('Content-Length: ' . filesize($filepath));

        //-- ダウンロード時のファイル名
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');

        //-- keep-aliveを無効にする
        header('Connection: close');

        //-- readfile()の前に出力バッファリングを無効化する ※詳細は後述
        while (ob_get_level()) { ob_end_clean(); }

        //-- 出力
        readfile($filepath);
        // echo file_get_contents($filepath);

        exit;

        // header('Location: ' . Bootstrap::APP_DIR . 'src/admin/csv.php');

        // exit();
    }

    /**
     *
     */
    public function createCSV(array $arr, int $mode, int $user_id = null) : string | false
    {
        //ファイル名生成
        $file_prefix = '';
        $top_arr = [];

        switch ($mode) {
            case self::OUTPUT_USERS:
                $file_prefix = 'users';
                $top_arr = ['ID', 'ユーザー名', '管理者フラグ', 'メールアドレス', '削除フラグ', '登録日', '更新日'];
                break;
            case self::OUTPUT_EVENTS:
                $file_prefix = 'events_user-id' . $user_id;
                $top_arr = [];
                break;
            default:
                $this->err_arr['mode_invalid'] = '不正なパラメータです。';
                return false;
                break;
        }
        array_unshift($arr, $top_arr);

        $filename = date('Y-m-d-H-i-s') . '_' . $file_prefix;
        $file_dir = Bootstrap::APP_DIR . 'csv/';
        $filepath = $this->nameFileWithoutCollision($file_dir, $filename);

        //ファイル作成
        $fp = fopen($filepath, 'w');
        foreach ($arr as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);

        return $filepath;
    }

    private function nameFileWithoutCollision(string $file_dir, string $filename) : string
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
