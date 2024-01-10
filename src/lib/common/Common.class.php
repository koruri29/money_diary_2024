<?php

namespace lib\common;

class Common
{
    const EMAIL_PATTERN = '/^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/';

    public static function h(string | array $before) : string | array
    {
        if (is_array($before))
        {
            $after =[];
            foreach($before as $key=>$val)
            {
                $after[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            }
            return $after;
        }
        else
        {
            $after = htmlspecialchars($before, ENT_QUOTES, 'UTF-8');
            return $after;
        }
    }
}
