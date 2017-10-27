<?php


namespace AppBundle\Utils;


class SaltGenerator
{

    public static function generateSalt()
    {
        return uniqid(mt_rand(), true);
    }
}

