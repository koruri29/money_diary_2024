<?php

namespace lib;

use lib\common\Common;
use lib\common\PDODatabase;
use lib\MoneyEvent;
use lib\SearchMoneyEvent;

class ManageMoneyEvent
{
    private $db;

    private MoneyEvent $event;

    public function __construct(PDODatabase $db)
    {
        $this->db = $db;
    }

    public function registerEvent() : bool
    {
        //バリデーション
        if (! $this->event->validateEvent()) return false;

        $table = ' money_events ';
        $insertData = [
            'user_id' => $this->event->getUserId(),
            'category_id' => $this->event->getCategoryId(),
            'wallet_id' => $this->event->getWalletId(),
            'option' => $this->event->getOption(),
            'amount' => $this->event->getAmount(),
            'date' => $this->event->getDate(),
            'other' => $this->event->getOther(),
        ];

        $res = $this->db->insert($table, $insertData);

        return $res;
    }

    public static function getAllEvents(PDODatabase $db) : array
    {
        $table = ' money_events e ';
        $column = <<<COL
            i.html_tag,
            e.wallet_id,
            e.option,
            e.amount,
            e.date,
            e.other,
            c.id AS category_id,
            c.category_name 
        COL;
        $join_table1 = ' categories c ';
        $join_on1 = ' c.id = e.category_id ';
        $join_table2 = ' icons i ';
        $join_on2 = ' c.icon_id = i.id ';
        
        $db->pushJoin($join_table1, $join_on1);
        $db->pushJoin($join_table2, $join_on2);
        $data = $db->select($table, $column);

        return $data;
    }

    public function updateEvent() : bool
    {
        if (! $this->event->validateEvent()) return false;

        $table = 'money_events ';
        $insertData = [
            'user_id' => $this->event->getUserId(),
            'category_id' => $this->event->getCategoryId(),
            'wallet_id' => $this->event->getWalletId(),
            'option' => $this->event->getOption(),
            'amount' => $this->event->getAmount(),
            'date' => $this->event->getDate(),
            'other' => $this->event->getOther(),
        ];
        $where = ' id = ? ';
        $arrWhereVal = [$this->event->getEventId()];

        $res = $this->db->update($table, $insertData, $where, $arrWhereVal);

        return $res;
    }

    public static function deleteEvent(PDODatabase $db, int $event_id) : bool
    {
        $table = 'money_events ';
        $res = $db->delete($table, ['id' => $event_id]);

        return $res;
    }

    /**
     * 入出金イベントの取得
     * 
     * @param int $user_id
     * @param bool $is_get_by_month トップ画面の月ごとの表示(not検索画面)か否か
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getEvents(int $user_id, bool $is_get_by_month, int $year = 0, int $month = 0) : array
    {
        $this->db->resetClause();

        $table = 'money_events e';
        $column = <<<COL
            e.id AS event_id, 
            e.category_id, 
            c.category_name,
            c.icon_id AS c_icon_id,
            i1.html_tag AS c_html,
            c.icon_color AS c_icon_color,
            e.wallet_id, 
            w.wallet_name, 
            w.icon_id AS w_icon_id, 
            i2.html_tag AS w_html, 
            w.icon_color AS w_icon_color,
            e.option,
            e.amount, 
            e.date, 
            e.other 
        COL;
        $where = ' e.user_id = ? ';
        $arr_val = [$user_id];

        //INNER JOIN
        $join_table1 = ' categories c ';
        $join_on1 = ' e.category_id = c.id ';
        $join_table2 = ' wallets w ';
        $join_on2 = ' e.wallet_id = w.id ';
        $join_table3 = ' icons i1 ';
        $join_on3 = ' COALESCE(c.icon_id, 0) = COALESCE(i1.id, 0) ';
        $join_table4 = ' icons i2 ';
        $join_on4 = ' COALESCE(w.icon_id, 0) = COALESCE(i2.id, 0) ';

        $this->db->pushJoin($join_table1, $join_on1);
        $this->db->pushJoin($join_table2, $join_on2);
        $this->db->pushJoin($join_table3, $join_on3);
        $this->db->pushJoin($join_table4, $join_on4);

        //WHERE句(日付)
        if ($is_get_by_month) {
            if ($year === 0) $year = date('Y');
            if ($month < 1 || $month > 12 || ! is_int($month)) $month = date('m');

            $datetime ='"' . $year . '-' . $month . '-01 00:00:00"';
            $where .= 'AND date BETWEEN ' . $datetime . ' AND LAST_DAY(' . $datetime . ') ';
        }

        //ORDER BY
        $this->db->setOrder('e.date DESC, e.created_at  DESC ');

        $events = $this->db->select($table, $column, $where, $arr_val);

        return $events;
    }

    public function getSum(int $user_id, bool $is_get_by_month, int $year = 0, int $month = 0) : int
    {
        $this->db->resetClause();

        $table = 'money_events';
        $column = <<<COL
            SUM(CASE WHEN option = 1 THEN amount END) AS income,
            SUM(CASE WHEN option = 0 THEN amount END) AS outgo 
        COL;
        $where = ' user_id = ? ';
        $arr_val = [$user_id];

        //WHERE句(日付)
        if ($is_get_by_month) {
            if ($year === 0) $year = date('Y');
            if ($month < 1 || $month > 12 || ! is_int($month)) $month = date('m');

            $datetime ='"' . $year . '-' . $month . '-01 00:00:00"';
            $where .= 'AND date BETWEEN ' . $datetime . ' AND LAST_DAY(' . $datetime . ') ';
        }

        $sums = $this->db->select($table, $column, $where, $arr_val);

        return $sums[0]['income'] - $sums[0]['outgo'];
    }

    public function searchEvents(SearchedEvent $s_event) : array
    {
        $this->db->resetClause();

        $table = 'money_events e';
        $column = <<<COL
            e.id AS event_id, 
            e.category_id, 
            c.category_name,
            c.icon_id AS c_icon_id,
            i1.html_tag AS c_html,
            c.icon_color AS c_icon_color,
            e.wallet_id, 
            w.wallet_name, 
            w.icon_id AS w_icon_id, 
            i2.html_tag AS w_html, 
            w.icon_color AS w_icon_color,
            e.option,
            e.amount, 
            e.date, 
            e.other 
        COL;
        $where = ' e.user_id = ? ';
        $arr_val = [$s_event->getUserId()];



        //INNER JOIN
        $join_table1 = ' categories c ';
        $join_on1 = ' e.category_id = c.id ';
        $join_table2 = ' wallets w ';
        $join_on2 = ' e.wallet_id = w.id ';
        $join_table3 = ' icons i1 ';
        $join_on3 = ' COALESCE(c.icon_id, 0) = COALESCE(i1.id, 0) ';
        $join_table4 = ' icons i2 ';
        $join_on4 = ' COALESCE(w.icon_id, 0) = COALESCE(i2.id, 0) ';

        $this->db->pushJoin($join_table1, $join_on1);
        $this->db->pushJoin($join_table2, $join_on2);
        $this->db->pushJoin($join_table3, $join_on3);
        $this->db->pushJoin($join_table4, $join_on4);

        //WHERE句
        //日付
        if (! empty($s_event->getMinDate() && $s_event->getMaxDate())) {
            $where .= ' AND date BETWEEN ? AND ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinDate(), $s_event->getMaxDate()]);
        } elseif (! empty($s_event->getMinDate())) {
            $where .= ' AND date >= ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinDate()]);
        } elseif (! empty( $s_event->getMaxDate())) {
            $where .= ' AND date <= ?';
            $arr_val = array_merge($arr_val, [$s_event->getMaxDate()]);
        }
        //金額
        if (! empty($s_event->getMinAmount() && $s_event->getMaxAmount())) {
            $where .= ' AND amount BETWEEN ? AND ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinAmount(), $s_event->getMaxAmount()]);
        } elseif (! empty($s_event->getMinAmount())) {
            $where .= ' AND amount >= ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinAmount()]);
        } elseif (! empty( $s_event->getMaxAmount())) {
            $where .= ' AND amount <= ?';
            $arr_val = array_merge($arr_val, [$s_event->getMaxAmount()]);
        }
        //カテゴリー
        if (! empty($s_event->getCategoryId())) {
            $where .= ' AND e.category_id = ? ';
            $arr_val = array_merge($arr_val, [$s_event->getCategoryId()]);
        }
        //検索ワード
        if (! empty($s_event->getOther())) {
            $words_arr = Common::adjustSearchWords($s_event->getOther());
            foreach ($words_arr as $word) {
                $where .= ' AND other LIKE ? ';
            }
            $arr_val = array_merge($arr_val, $words_arr);
        }
        //収入or支出
        if ($s_event->getOption() === 0) {
            $where .= ' AND option = 0 ';
        } elseif ($s_event->getOption() === 1) {
            $where .= ' AND option = 1 ';
        } elseif ($s_event->getOption() === 2) {
            $where .= ' AND option = 2 ';
        } else {
            // do nothing
        }

        //ORDER BY
        $this->db->setOrder('e.date DESC, e.created_at DESC ');

        $events = $this->db->select($table, $column, $where, $arr_val);

        return $events;
    }

    public function getSearchedSum(SearchedEvent $s_event) : int
    {
        $this->db->resetClause();

        $table = 'money_events';
        $column = <<<COL
            SUM(CASE WHEN option = 1 THEN amount END) AS income,
            SUM(CASE WHEN option = 0 THEN amount END) AS outgo 
        COL;
        $where = ' user_id = ? ';
        $arr_val = [$s_event->getUserId()];

        //WHERE句
        //日付
        if (! empty($s_event->getMinDate() && $s_event->getMaxDate())) {
            $where .= ' AND date BETWEEN ? AND ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinDate(), $s_event->getMaxDate()]);
        } elseif (! empty($s_event->getMinDate())) {
            $where .= ' AND date >= ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinDate()]);
        } elseif (! empty( $s_event->getMaxDate())) {
            $where .= ' AND date <= ?';
            $arr_val = array_merge($arr_val, [$s_event->getMaxDate()]);
        }
        //金額
        if (! empty($s_event->getMinAmount() && $s_event->getMaxAmount())) {
            $where .= ' AND amount BETWEEN ? AND ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinAmount(), $s_event->getMaxAmount()]);
        } elseif (! empty($s_event->getMinAmount())) {
            $where .= ' AND amount >= ? ';
            $arr_val = array_merge($arr_val, [$s_event->getMinAmount()]);
        } elseif (! empty( $s_event->getMaxAmount())) {
            $where .= ' AND amount <= ?';
            $arr_val = array_merge($arr_val, [$s_event->getMaxAmount()]);
        }
        //カテゴリー
        if (! empty($s_event->getCategoryId())) {
            $where .= ' AND category_id = ? ';
            $arr_val = array_merge($arr_val, [$s_event->getCategoryId()]);
        }
        //検索ワード
        if (! empty($s_event->getOther())) {
            $words_arr = Common::adjustSearchWords($s_event->getOther());
            foreach ($words_arr as $word) {
                $where .= ' AND other LIKE ? ';
            }
            $arr_val = array_merge($arr_val, $words_arr);
        }
        //収入or支出
        if ($s_event->getOption() === 0) {
            $where .= ' AND option = 0 ';
        } elseif ($s_event->getOption() === 1) {
            $where .= ' AND option = 1 ';
        } elseif ($s_event->getOption() === 2) {
            $where .= ' AND option = 2 ';
        } else {
            // do nothing
        }

        $sums = $this->db->select($table, $column, $where, $arr_val);

        return $sums[0]['income'] - $sums[0]['outgo'];
    }

    public function setEvent(MoneyEvent $event) : void
    {
        $this->event = $event;
    }
}
