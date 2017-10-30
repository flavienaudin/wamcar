<?php


namespace AppBundle\Utils;


class TokenGenerator
{
    /**
     * Return a new token
     *
     * @return string
     */
    public static function generateToken()
    {
        return md5(uniqid("", true));
    }

    public static function generateSalt()
    {
        return uniqid(mt_rand(), true);
    }
}
