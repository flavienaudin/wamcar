<?php

namespace AppBundle\DTO;

interface BuilderFromDTO
{
    /**
     * @param mixed $dto
     * @return mixed
     */
    public function buildFromDTO($dto);
}
