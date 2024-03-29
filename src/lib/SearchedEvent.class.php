<?php

namespace lib;

use lib\common\PDODatabase;

class SearchedEvent
{
    private int $event_id;

    private int $user_id;

    private int $category_id;

    private int $wallet_id;

    private int $option;// 収入or支出

    private int $min_amount;// 最小金額

    private int $max_amount;// 最大金額

    private string $min_date;

    private string $max_date;

    private string $other;// 備考

    private array $err_arr = [];

    public function __construct(
        int $user_id,
        int $category_id,
        int $wallet_id,
        int $option,
        int $min_amount,
        int $max_amount,
        string $min_date,
        string $max_date,
        string $other,  
    )
    {
        $this->user_id = $user_id;
        $this->category_id = $category_id;
        $this->wallet_id = $wallet_id;
        $this->option = $option;
        $this->min_amount = $min_amount;
        $this->max_amount = $max_amount;
        $this->min_date = $min_date;
        $this->max_date = $max_date;
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

        if ($this->option === 0 ||$this->option === 1 ||$this->option === 2 ||$this->option === 99) {
            //do nothing
        } else {
            $this->err_arr['wallet_id_invalid'] = '有効なオプション値を入力してください。';
            $flg = false;
        }
        
        if (! is_int($this->min_amount) && ! is_int($this->max_amount)) {
            $this->err_arr['amount_not_int'] = '金額は正の整数で入力してください。';
            $flg = false;
        } elseif($this->min_amount < 1 && $this->max_amount < 1) {
            $this->err_arr['amount_minus'] = '金額は正の整数で入力してください。';
            $flg = false;
        }

        $date_start = strtotime('1900-01-01');
        $date_end = strtotime('2100-12-31');
        if (strtotime($this->min_date) < $date_start || strtotime($this->min_date) > $date_end ||
        strtotime($this->max_date) < $date_start || strtotime($this->max_date) > $date_end) {
            $this->err_arr['date_invalid'] = '有効な日付を指定してください。';
            $flg = false;
        }

        return $flg;
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
    
    public function getMinAmount() : int
    {
        return $this->min_amount;
    }

    public function getMaxAmount() : int
    {
        return $this->max_amount;
    }
    
    public function getMinDate() : string
    {
        return $this->min_date;
    }
    
    public function getMaxDate() : string
    {
        return $this->max_date;
    }

    public function getOther() : string
    {
        return $this->other;
    }
    
    public function getErrArr() : array
    {
        return $this->err_arr;
    }
}
