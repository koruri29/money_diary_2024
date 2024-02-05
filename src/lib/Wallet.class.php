<?php

namespace lib;

use lib\common\PDODatabase;

class Wallet
{

    public static function getWalletsByUserId (PDODatabase $db, int $user_id) : array
    {
        $db->resetClause();

        $table = ' wallets w ';
        $column = <<<COL
            w.id AS wallet_id,
            w.wallet_name,
            w.item_order,
            w.icon_id,
            w.icon_color,
            i.html_tag AS i_html,
            i.icon_name 
        COL;
        $where = ' w.user_id = ? ';
        $arr_val = [$user_id];

        $join_table = ' icons i';
        $join_on = 'i.id = w.icon_id ';

        $db->pushJoin($join_table, $join_on);
        
        $wallets = $db->select($table, $column, $where, $arr_val);

        return $wallets;
    }
    /**
     * 新規ユーザー登録時の、ユーザーに紐づいたカテゴリ登録
     */
    public static function initWallets(PDODatabase $db, int $user_id) : bool {
        $table = ' wallets ';
        $insertDataCol = [
            'user_id',
            'wallet_name',
        ];
        $insertDataValArr = [
            [$user_id, '現金'],
        ];

        $res = $db->repeatInsert($table, $insertDataCol, $insertDataValArr);

        return $res;
    }
}
