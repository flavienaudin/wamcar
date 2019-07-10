<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    /** Configure command */
    const DATE_FORMAT = "d-m-Y H:i:s";
}
