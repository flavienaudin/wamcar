<?php


namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;

class TestsExtension extends AbstractExtension
{

    public function getTests()
    {
        return array(
            new \Twig_Test('instanceof', array($this, 'isInstanceOfTest'))
        );
    }

    /**
     * @param mixed $value
     * @param string $className
     * @return bool
     */
    public function isInstanceOfTest($value, string $className)
    {
        return $value instanceof $className;
    }
}
