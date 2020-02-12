<?php


namespace Wamcar\User;


interface ProUserProServiceRepository
{
    /**
     * @param ProUserProService $proUserProService
     */
    public function remove(ProUserProService $proUserProService): void;

    /**
     * @param array $proUserProService
     * @param int $batchSize
     */
    public function removeBulk(array $proUserProServices, ?int $batchSize = 50);

}