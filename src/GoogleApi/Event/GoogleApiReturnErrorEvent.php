<?php

namespace GoogleApi\Event;


interface GoogleApiReturnErrorEvent
{

    /**
     * GoogleApiReturnError constructor.
     * @param string $returnStatus
     * @param string $message
     * @param array|null $callParams
     */
    public function __construct(string $returnStatus, string $message, array $callParams = []);

    /**
     * @return string
     */
    public function getReturnStatus(): string;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @return array
     */
    public function getCallParams(): array;
}