<?php


namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class TestsExtension extends AbstractExtension
{

    public function getTests()
    {
        return array(
            new TwigTest('instanceof', array($this, 'isInstanceOfTest'))
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
