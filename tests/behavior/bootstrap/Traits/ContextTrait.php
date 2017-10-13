<?php

namespace Test\Behavior\Context\Traits;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait ContextTrait
{
    /** @var \Behat\MinkExtension\Context\MinkContext */
    private $minkContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
    }
}
