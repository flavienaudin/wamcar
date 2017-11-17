<?php

namespace Test\Behavior\Context;

use AppBundle\Controller\Front\BaseController;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ramsey\Uuid\Uuid;

class SecurityContext extends BaseContext
{
    use KernelDictionary;

    use Traits\ContextTrait;


    /**
     * @Given I am not logged in
     */
    public function I am not logged in()
    {
        $this->minkContext->visit('/deconnexion');
    }

    /**
     * @Given I log in as user
     */
    public function I log in as user()
    {
        $this->minkContext->visit('/');
        $this->findByCssSelector( '#open-side-login')->click();
        $this->fillLoginFormSide('cedric@novaway.fr', 'azerty');
        $this->findByCssSelector( '#form-side-login-submit')->click();
    }

    /**
     * @Given I fill the connexion form on dedicated page as user
     */
    public function I fill the connexion form on dedicated page as user()
    {
        $this->minkContext->visit('/connexion');
        $this->fillLoginForm('cedric@novaway.fr','azerty');
        $this->findByCssSelector( '#form-login-submit')->click();
    }

    /**
     * @Given I log in with wrong email
     */
    public function I log in with wrong email()
    {
        $this->findByCssSelector( '#open-side-login')->click();

        $this->fillLoginFormSide('wrongemails@novaway.fr', 'wrongpassword');

        $this->findByCssSelector( '#form-side-login-submit')->click();
    }

    /**
     * @Given I log in with wrong password
     */
    public function I log in with wrong password()
    {
        $this->findByCssSelector( '#open-side-login')->click();

        $this->fillLoginFormSide('cedric@novaway.fr', 'wrongpassword');

        $this->findByCssSelector( '#form-side-login-submit')->click();
    }

    /**
     * @Given I register as personal user
     */
    public function I register as personal user()
    {
        $this->minkContext->visit('/inscription');
        $password = '123456';


        // fill mandatory fields
        $this->minkContext->fillField($this->getFieldName('registration', 'email'), Uuid::uuid4() . '@novaway.fr');
        $this->minkContext->fillField($this->getFieldName('registration', 'password') . '[first]', $password);
        $this->minkContext->fillField($this->getFieldName('registration', 'password') . '[second]', $password);
        $this->findByCssSelector('label[for="registration_accept"]')->click();
        //submit
        $this->findByCssSelector('form[name="registration"] button[type="submit"]')->click();

    }

    /**
     * @Given I register as pro user
     */
    public function I register as pro user()
    {
        $this->minkContext->visit('/inscription/pro');
        $password = '123456';


        // fill mandatory fields
        $this->minkContext->fillField($this->getFieldName('registration', 'email'), Uuid::uuid4() . '@novaway.fr');
        $this->minkContext->fillField($this->getFieldName('registration', 'password') . '[first]', $password);
        $this->minkContext->fillField($this->getFieldName('registration', 'password') . '[second]', $password);
        $this->findByCssSelector('label[for="registration_accept"]')->click();
        //submit
        $this->findByCssSelector('form[name="registration"] button[type="submit"]')->click();

    }

    /**
     * @Given I register as an existing user
     */
    public function I register as an existing user()
    {
        $this->minkContext->visit('/inscription');

        $password = '123456';
        // fill mandatory fields
        $this->minkContext->fillField($this->getFieldName('registration', 'email'), 'cedric@novaway.fr');
        $this->minkContext->fillField($this->getFieldName('registration', 'password') . '[first]', $password);
        $this->minkContext->fillField($this->getFieldName('registration', 'password') . '[second]', $password);
        $this->findByCssSelector('label[for="registration_accept"]')->click();
        //submit
        $this->findByCssSelector('form[name="registration"] button[type="submit"]')->click();

    }


    /**
     * @Given I submit an empty form
     */
    public function I submit an empty form()
    {
        $this->minkContext->visit('/inscription');
        $this->findByCssSelector('form[name="registration"] button[type="submit"]')->click();
    }


    /**
     * @param $username
     * @param $password
     */
    private function fillLoginFormSide(string $username, string $password)
    {
        $this->minkContext->fillField('email_side', $username);
        $this->minkContext->fillField('password_side', $password);
    }

    /**
     * @param $username
     * @param $password
     */
    private function fillLoginForm(string $username, string $password)
    {
        $this->minkContext->fillField('email_login', $username);
        $this->minkContext->fillField('password_login', $password);
    }

    /**
     * @Then I should see an error message
     */
    public function I should see an error message()
    {
        return $this->minkContext->assertElementOnPage('.callout.' .BaseController::FLASH_LEVEL_DANGER);
    }

    /**
     * @Then I should see a confirmation message
     */
    public function I should see a confirmation message()
    {
        return $this->minkContext->assertElementOnPage('.callout.' .BaseController::FLASH_LEVEL_INFO);
    }

    /**
     * @Then I should see error messages for all mandatory fields
     */
    public function I should see error messages for all mandatory fields()
    {
        $this->minkContext->assertNumElements(4,'.form-error.is-visible');
    }

}
