<?php

namespace AppBundle\EntityListener;


use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DoctrineFilterListener
{

    public function onKernelRequest(GetResponseEvent $event)
    {

        if ('easyadmin' === $event->getRequest()->attributes->get('_route')) {
            return;
        }
        $this->em->getFilters()->enable('is_published');
    }
}