<?php

namespace lib;

use lib\common\PDODatabase;

class MoneyEvent
{
    private int $event_id;

    private int $user_id;

    private int $category_id;

    private int $wallet_id;

    private int $option;// 収入or支出

    private int $amount;// 金額

    private string $date;

    private string $other;// 備考

    private array $err_arr = [];

    public function __construct(
        int $user_id,
        int $category_id,
        int $wallet_id,
        int $option,
        int $amount,
        string $date,
        string $other,  
    )
    {
        $this->user_id = $user_id;
        $this->category_id = $category_id;
        $this->wallet_id = $wallet_id;
        $this->option = $option;
        $this->amount = $amount;
        $this->date = $date;
        $this->other = $other;
    }

    public function validateEvent() : bool
    {
        $flg = true;

        if (! is_int($this->user_id)) {
            $this->err_arr['user_id_not_int'] = 'ユーザーIDは正の整数で入力してください。';
            $flg = false;
        } elseif($this->user_id < 1) {
            $this->err_arr['user_id_minus'] = 'ユーザーIDは正の整数で入力してください。';
            $flg = false;
        }

        if (! is_int($this->category_id)) {
            $this->err_arr['category_id_not_int'] = 'カテゴリーIDは正の整数で入力してください。';
            $flg = false;
        } elseif($this->category_id < 1) {
            $this->err_arr['category_id_minus'] = 'カテゴリーIDは正の整数で入力してください。';
            $flg = false;
        }

        if (! is_int($this->wallet_id)) {
            $this->err_arr['wallet_id_not_int'] = '財産IDは正の整数で入力してください。';
            $flg = false;
        } elseif($this->wallet_id < 1) {
            $this->err_arr['wallet_id_minus'] = '財産IDは正の整数で入力してください。';
            $flg = false;
        }

        if ($this->option !== 0 && $this->option !== 1) {
            $this->err_arr['wallet_id_invalid'] = '有効なオプション値を入力してください。';
            $flg = false;
        }
        
        if (! is_int($this->amount)) {
            $this->err_arr['amount_not_int'] = '金額は正の整数で入力してください。';
            $flg = false;
        } elseif($this->amount < 1) {
            $this->err_arr['amount_minus'] = '金額は正の整数で入力してください。';
            $flg = false;
        }

        $date_start = strtotime('1950-01-01');
        $date_end = strtotime('2050-12-31');
        if (strtotime($this->date) < $date_start || strtotime($this->date) > $date_end) {
            $this->err_arr['date_invalid'] = '有効な日付を指定してください。';
            $flg = false;
        }

        return $flg;
    }

    public static function getEventById(PDODatabase $db, int $event_id) : ?MoneyEvent
    {
        $table = ' money_events ';
        $column = <<<COL
            id,
            user_id,
            category_id,
            wallet_id,
            option,
            amount,
            date,
            other 
        COL;
        $where = 'id = ? ';
        $arr_val = [$event_id];

        $event_info = $db->select($table, $column, $where, $arr_val);

        if (empty($event_info)) return null;

        $event = new MoneyEvent(
            $event_info[0]['user_id'],
            $event_info[0]['category_id'],
            $event_info[0]['wallet_id'],
            $event_info[0]['option'],
            $event_info[0]['amount'],
            $event_info[0]['date'],
            $event_info[0]['other'],    
        );
        $event->setEventId($event_info[0]['id']);

        return $event;
    }

    public function setEventId(int $event_id) : void
    {
        if (! is_int($event_id) || $event_id < 1) {
            $this->err_arr['event_id_not_int'] = 'イベントIDは正の整数で入力してください。';
            return;
        }
        $this->event_id = $event_id;
    }

    public function getEventId() : int
    {
        return $this->event_id;
    }
    
    public function getUserId() : int
    {
        return $this->user_id;
    }
    
    public function getCategoryId() : int
    {
        return $this->category_id;
    }
    
    public function getWalletId() : int
    {
        return $this->wallet_id;
    }
    
    public function getOption() : int
    {
        return $this->option;
    }
    
    public function getAmount() : int
    {
        return $this->amount;
    }
    
    public function getDate() : string
    {
        return $this->date;
    }
    
    public function getOther() : string
    {
        return $this->other;
    }
    
    public function getErrArr()
    {
        return $this->err_arr;
    }
}
