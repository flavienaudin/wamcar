<?php

namespace Test\Behavior\Context;

use Behat\Symfony2Extension\Context\KernelDictionary;

class RegisterContext extends BaseContext
{
    use KernelDictionary;

    use Traits\ContextTrait;

    /**
     * @Given /^I register with plate '([^']+)'$/
     */
    public function I register with plate($value)
    {
        $this->fillPlateForm($value);
        $this->findByCssSelector('form[name="form-plate-number"] button[type="submit"]')->click();
    }

    /**
     * @Then /^field '([^']+)' is equals to '([^']+)'$/
     */
    public function field is equal($field, $value)
    {
        $this->minkContext->assertFieldContains($this->getFieldName('vehicle[information]', $field), $value);
    }

    /**
     * @param string $plateNumber
     */
    private function fillPlateForm(string $plateNumber)
    {
        $this->minkContext->fillField('plate_number', $plateNumber);
    }
}
