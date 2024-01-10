<?php

namespace lib\common;

class Token
{
    private static string $token;

    public static function generateToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
