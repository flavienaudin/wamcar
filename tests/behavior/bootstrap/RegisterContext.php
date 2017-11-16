<?php

namespace Test\Behavior\Context;

use Behat\Symfony2Extension\Context\KernelDictionary;

class RegisterContext extends BaseContext
{
    use KernelDictionary;

    use Traits\ContextTrait;


    /**
     * @Given I choose an make
     */
    public function I choose an make()
    {
        $makeFieldName = $this->getFieldName('vehicle[information]', 'make');
        $makeField = $this->getSession()->getPage()->findField($makeFieldName);
        $makeField->selectOption('PEUGEOT');
        //$this->minkContext->selectOption($makeField, 'PEUGEOT');

        $this->spins(function() {
            //$this->minkContext->selectOption($this->getFieldName('vehicle[information]', 'model'), '405 II');
            //dump(count($optionElements));
        }, 10);
        //$modelField = $this->getFieldName('vehicle[information]', 'model');
        //$this->minkContext->selectOption($modelField, '405 II');
    }

    /**
     * @Given I choose an model
     */
    public function I choose an model()
    {
        $modelField = $this->getFieldName('vehicle[information]', 'model');
        $this->minkContext->selectOption($modelField, '405 II');
    }


}
