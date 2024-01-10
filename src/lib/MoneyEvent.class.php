<?php

namespace lib;

use lib\common\PDODatabase;

class MoneyEvent
{
    private int $event_id;

    private int $user_id;

    private int $category_id;

    private int $wallet_id;

    private int $option;

    private int $amount;

    private string $date;

    private string $other;

    private array $err_arr = [];

    public function __construct(
        $user_id,
        $category_id,
        $wallet_id,
        $option,
        $amount,
        $date,
        $other,  
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

        if (! $this->option === 0 && $this->option === 1) {
            $this->err_arr['wallet_id_invalid'] = '有効なオプション値を入力してください。';
            $flg = false;
        }
        
        if (! is_int($this->amount)) {
            $this->err_arr['amount_not_int'] = '財産IDは正の整数で入力してください。';
            $flg = false;
        } elseif($this->wallet_id < 1) {
            $this->err_arr['amount_minus'] = '財産IDは正の整数で入力してください。';
            $flg = false;
        }

        return $flg;
    }

    public static function getEventById(PDODatabase $db, int $event_id) : ?MoneyEvent
    {
        $table = ' money_event ';
        $column = <<<COL
            event_id,
            user_id,
            category_id,
            wallet_id,
            option,
            amount,
            date,
            other
        COL;
        $where = 'event_id = ? ';
        $arr_val = [$event_id];

        $event = $db->select($table, $column, $where, $arr_val);

        if (empty($event_info)) return null;

        $event = new MoneyEvent(
            $event[0]['user_id'],
            $event[0]['category_id'],
            $event[0]['wallet_id'],
            $event[0]['option'],
            $event[0]['amount'],
            $event[0]['date'],
            $event[0]['other'],    
        );
        $event->setEventId($event[0]['event_id']);

        return $event;
    }

    private function setEventId(int $event_id) : void
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
