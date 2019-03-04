<?php

namespace AppBundle\EventListener;


use AppBundle\Annotation\IgnoreSoftDeleted;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class AnnotationListener
{
    /** @var Reader */
    protected $reader;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(Reader $reader, EntityManagerInterface $entityManager)
    {
        $this->reader = $reader;
        $this->entityManager = $entityManager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }
        list($controller, $method,) = $controller;

        $this->ignoreSoftDeletedAnnotation($controller, $method);
    }

    private function readAnnotation($controller, $method, $annotation)
    {
        $classReflection = new \ReflectionClass($controller);
        $classAnnotation = $this->reader->getClassAnnotation($classReflection, $annotation);
        $objectReflection = new \ReflectionObject($controller);
        $methodReflection = $objectReflection->getMethod($method);
        $methodAnnotation = $this->reader->getMethodAnnotation($methodReflection, $annotation);

        if (!$classAnnotation && !$methodAnnotation) {
            return false;
        }

        return [$classAnnotation, $classReflection, $methodAnnotation, $methodReflection];
    }

    private function ignoreSoftDeletedAnnotation($controller, $method)
    {
        static $class = IgnoreSoftDeleted::class;
        $readAnnotation = $this->readAnnotation($controller, $method, $class);
        if ($readAnnotation) {
            $this->entityManager->getFilters()->disable('softDeleteable');
        }
    }
}