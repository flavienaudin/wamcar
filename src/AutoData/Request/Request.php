<?php

namespace AutoData\Request;

interface Request
{
    /**
     * @return string
     */
    public function getName(): string;
    
    /**
     * @return string
     */
    public function getParams(): array;
}
