<?php

namespace lib;

use lib\common\PDODatabase;
use lib\MoneyEvent;

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
            c.category_id,
            c.category_name 
        COL;
        $join_table1 = ' categories c ';
        $join_on1 = ' c.category_id = e.category_id ';
        $join_table2 = ' icons i ';
        $join_on2 = ' c.icon_id = i.icon_id ';
        
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
            'wallet_id' => $this->event->getWalletId(),
            'option' => $this->event->getOption(),
            'amount' => $this->event->getAmount(),
            'date' => $this->event->getDate(),
            'other' => $this->event->getOther(),
        ];
        $where = ' event_id = ? ';
        $arrWhereVal = [$this->event->getEventId()];

        $res = $this->db->update($table, $insertData, $where, $arrWhereVal);

        return $res;
    }

    public function deleteEvent($event_id) : bool
    {
        $table = 'money_events ';
        $column = ' event_id ';

        $res = $this->db->delete($table, $column, $event_id);

        return $res;
    }

    /**
     * 入出金イベントの取得
     * 
     * @param int $user_id
     * @param bool $isGetByMonth トップ画面の月ごとの表示(not検索画面)か否か
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getEvents(int $user_id, bool $isGetByMonth, int $year = 0, int $month = 0) : array
    {
        $table = 'money_events e';
        $column = <<<COL
            e.event_id, 
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

        $join_table1 = ' categories c ';
        $join_on1 = ' e.category_id = c.category_id ';
        $join_table2 = ' wallets w ';
        $join_on2 = ' e.wallet_id = w.wallet_id ';
        $join_table3 = ' icons i1 ';
        $join_on3 = ' COALESCE(w.icon_id, 0) = COALESCE(i1.icon_id, 0) ';
        $join_table4 = ' icons i2 ';
        $join_on4 = ' COALESCE(w.icon_id, 0) = COALESCE(i2.icon_id, 0) ';

        $this->db->pushJoin($join_table1, $join_on1);
        $this->db->pushJoin($join_table2, $join_on2);
        $this->db->pushJoin($join_table3, $join_on3);
        $this->db->pushJoin($join_table4, $join_on4);

        if ($isGetByMonth) {
            if ($year === 0) $year = date('Y');
            if ($month < 1 || $month > 12 || ! is_int($month)) $month = date('m');

            $datetime ='"' . $year . '-' . $month . '-01 00:00:00"';
            $where .= 'AND date BETWEEN ' . $datetime . ' AND LAST_DAY(' . $datetime . ') ';
        }

        $events = $this->db->select($table, $column, $where, $arr_val);

        return $events;
    }

    public function setEvent(MoneyEvent $event) : void
    {
        $this->event = $event;
    }
}
