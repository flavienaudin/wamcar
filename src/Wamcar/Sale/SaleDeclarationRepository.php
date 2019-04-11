<?php

namespace Wamcar\Sale;


interface SaleDeclarationRepository
{

    /**
     * @param Declaration $declaration
     * @return Declaration
     */
    public function add(Declaration $declaration): Declaration;

    /**
     * @param Declaration $declaration
     * @return Declaration
     */
    public function update(Declaration $declaration): Declaration;

    /**
     * @param Declaration $declaration
     * @return boolean
     */
    public function remove(Declaration $declaration);

}