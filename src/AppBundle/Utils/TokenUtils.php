<?php


namespace AppBundle\Utils;


class TokenUtils
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
}
