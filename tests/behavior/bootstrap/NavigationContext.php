<?php

namespace Test\Behavior\Context;

use Behat\Symfony2Extension\Context\KernelDictionary;

class NavigationContext extends BaseContext
{
    use KernelDictionary;

    use Traits\ContextTrait;

    /**
     * @param string $name
     * @param array $parameters
     * @return string
     */
    private function getRoute(string $name, array $parameters = []): string
    {
        return $this->getContainer()->get('router')->generate($name, $parameters);
    }
}
