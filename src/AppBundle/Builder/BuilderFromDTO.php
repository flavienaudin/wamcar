<?php

namespace AppBundle\Builder;

interface BuilderFromDTO
{
    /**
     * @param mixed $dto
     * @return mixed
     */
    public function buildFromDTO($dto);
}
