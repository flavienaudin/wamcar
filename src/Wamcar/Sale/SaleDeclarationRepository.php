<?php

namespace Wamcar\Sale;


use Wamcar\User\ProUser;

interface SaleDeclarationRepository
{
    /**
     * @param ProUser $user L'utilisateur ayant fait les ventes
     * @param int|null $sinceDays Intervalle de temps avant la date de référence
     * @param \DateTimeInterface|null $referenceDate Date de référence (par défaut le jour même)
     * @return int
     */
    public function getCountSales(ProUser $proUser, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int;

    /**
     * @param ProUser $user L'utilisateur ayant fait les reprises
     * @param int|null $sinceDays Intervalle de temps avant la date de référence
     * @param \DateTimeInterface|null $referenceDate Date de référence (par défaut le jour même)
     * @return int
     */
    public function getCountPartExchanges(ProUser $proUser, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int;

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