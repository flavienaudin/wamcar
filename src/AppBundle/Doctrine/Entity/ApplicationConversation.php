<?php

namespace AppBundle\Doctrine\Entity;

use Gedmo\Timestampable\Traits\Timestampable;
use Wamcar\Conversation\Conversation;

class ApplicationConversation extends Conversation
{
    use Timestampable;
}
