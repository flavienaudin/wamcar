<?php


namespace Wamcar\User;


interface ProServiceCategoryRepository
{

    /**
     * @param ProServiceCategory $proServiceCategory
     * @return boolean
     */
    public function remove(ProServiceCategory $proServiceCategory);

}