<?php

namespace AutoData\Exception;

use GuzzleHttp\Exception\ServerException;
use Throwable;

class WebserviceCallException extends \RuntimeException implements AutodataException
{
    /**
     * WebserviceCallException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = sprintf('An error (%d) occured while connecting to Autodata webservice : "%s"', $message, $code);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param ServerException $serverException
     * @return WebserviceCallException
     */
    public static function buildFromServerException(ServerException $serverException): self
    {
        return new self($serverException->message, $serverException->getCode(), $serverException);
    }
}
