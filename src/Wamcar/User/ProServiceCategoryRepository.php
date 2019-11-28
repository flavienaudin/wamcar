<?php


namespace Wamcar\User;


interface ProServiceCategoryRepository
{

    /**
     * @param ProService $proService
     * @return boolean
     */
    public function remove(ProService $proService);

}