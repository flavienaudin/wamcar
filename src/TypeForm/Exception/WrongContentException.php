<?php

namespace TypeForm\Exception;


class WrongContentException extends \InvalidArgumentException implements TypeFormException
{

    /**
     * WrongContentException constructor.
     */
    public function __construct(string $errorKey)
    {
        parent::__construct(sprintf('Error %s', $errorKey));
    }
}