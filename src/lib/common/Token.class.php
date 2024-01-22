<?php

namespace lib\common;

class Token
{
    public static function generateToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
