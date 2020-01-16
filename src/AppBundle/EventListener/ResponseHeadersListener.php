<?php


namespace AppBundle\EventListener;


use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseHeadersListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $responseHeaders = $event->getResponse()->headers;
        if(!$responseHeaders->has('X-Frame-Options')) {
            // Forbidden to embed our site into <iframe>
            // Specific configuration should be set into controller actions
            $responseHeaders->set('X-Frame-Options', 'DENY');
        }
    }
}