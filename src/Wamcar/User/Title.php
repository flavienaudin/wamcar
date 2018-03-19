<?php

namespace Wamcar\User;

use MyCLabs\Enum\Enum;

/**
 * @method static Title MR()
 * @method static Title MS()
 */
class Title extends Enum
{
    const MR = 'Monsieur';
    const MS = 'Madame';

    /**
     * @param $gender {"male","female"}
     * @return Title|null
     */
    public static function convertGender($gender)
    {
        switch ($gender) {
            case "male":
                return self::MR();
            case "female":
                return self::MS();
        }
        return null;
    }
}
