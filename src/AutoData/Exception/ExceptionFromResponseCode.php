<?php

namespace AutoData\Exception;

class ExceptionFromResponseCode
{
    private static $exceptions = [
        '34' => RegistrationNumberNotFoundException::class,
        '103' => InvalidRegistrationNumberException::class,
        ];

    /**
     * @param string $code
     * @param string $message
     * @return AutodataException|null
     */
    public static function get(string $code, string $message): ?AutodataException
    {
        if($code === '0') {
            return null;
        }

        $exceptionClass = self::$exceptions[$code] ?? AutodataNotManagedException::class;

        return new $exceptionClass($message);
    }
}
