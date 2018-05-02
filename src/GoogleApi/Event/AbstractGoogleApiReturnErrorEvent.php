<?php

namespace GoogleApi\Event;


abstract class AbstractGoogleApiReturnErrorEvent
{

    /** @var string */
    private $returnStatus;
    /** @var string */
    private $message;
    /** @var array */
    private $callParams;

    /**
     * GoogleApiReturnError constructor.
     * @param string $returnStatus
     * @param string $message
     * @param array|null $callParams
     */
    public function __construct(string $returnStatus, string $message, array $callParams = [])
    {
        $this->returnStatus = $returnStatus;
        $this->message = $message;
        $this->callParams = $callParams;
    }

    /**
     * @return string
     */
    public function getReturnStatus(): string
    {
        return $this->returnStatus;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getCallParams(): array
    {
        return $this->callParams;
    }
}