<?php

namespace lib\common;

class Common
{
    const EMAIL_PATTERN = '/^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/';

    /**
     * 文字列or1次元配列のサニタイズ
     * 2次元配列の場合はCommon::wh()を使用
     * 
     * @param string|array $before サニタイズ前
     * @return string|array $after サニタイズ後
     */
    public static function h(string | array $before) : string | array
    {
        if (is_array($before)) {
            $after =[];
            foreach($before as $key=>$val) {
                //DBからアイコンを取ってきた場合は、サニタイズをスキップ
                if (preg_match('/^(._html)$/', $key) === 1) {
                    $after[$key] = $val;
                    continue;
                }

                $after[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            }
            return $after;
        } else {
            $after = htmlspecialchars($before, ENT_QUOTES, 'UTF-8');
            return $after;
        }
    }

    /**
     * 二重配列の場合のサニタイズ
     * 
     * @param array $w_array 二重配列
     * @return array サニタイズ後の配列
     */
    public static function wh(array $w_array) : array
    {
        if (! is_array($w_array)) return self::h($w_array);

        foreach ($w_array as $key => $array) {
            $w_array[$key] = self::h($array);
        }

        return $w_array;
    }
}
