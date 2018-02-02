<?php

namespace AppBundle\Doctrine\Entity;

use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Timestampable\Traits\Timestampable;
use Wamcar\Conversation\Conversation;
use Wamcar\Garage\Garage;

class ApplicationConversation extends Conversation
{
    use Timestampable;

    /** @var \DateTime */
    protected $createdAt;
    /** @var \DateTime*/
    protected $updatedAt;

}
