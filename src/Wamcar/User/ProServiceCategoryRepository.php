<?php


namespace Wamcar\User;


interface ProServiceCategoryRepository
{

    /**
     * @return array
     */
    public function findEnabledOrdered();

    /**
     * @param ProServiceCategory $proServiceCategory
     * @return boolean
     */
    public function remove(ProServiceCategory $proServiceCategory);

}