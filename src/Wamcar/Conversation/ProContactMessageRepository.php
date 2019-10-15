<?php

namespace Wamcar\Conversation;


interface ProContactMessageRepository
{

    /**
     * @param ProContactMessage $proContactMessage
     * @return ProContactMessage
     */
    public function add(ProContactMessage $proContactMessage);

    /**
     * @param ProContactMessage $proContactMessage
     * @return ProContactMessage
     */
    public function update(ProContactMessage $proContactMessage);

    /**
     * @param ProContactMessage $proContactMessage
     * @return ProContactMessage
     */
    public function remove(ProContactMessage $proContactMessage);

    /**
     * Finds entities by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
}
