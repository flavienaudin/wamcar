<?php


namespace Wamcar\User;


interface ProUserProServiceRepository
{
    /**
     * @param ProUserProService $proUserProService
     */
    public function remove(ProUserProService $proUserProService): void;

}