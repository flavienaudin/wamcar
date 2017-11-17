Feature: Control security of the website

    @security @login
    Scenario: Log in with correct credentials
        Given I am not logged in
        And I log in as user
        Then the url should match "/mon-profil"

    @security @log-in-on-login-page
    Scenario: Log in on dedicated login page
        Given I am not logged in
        And I fill the connexion form on dedicated page as user
        Then the url should match "/mon-profil"

    @security @login-wrong-email
    Scenario: Log in with wrong email
        Given I am not logged in
        And I log in with wrong email
        Then I should see an error message

    @security @login-wrong-password
    Scenario: Log in with wrong password
        Given I am not logged in
        And I log in with wrong password
        Then I should see an error message

    @security @register
    Scenario: Create an personal user account
        Given I am not logged in
        And I register as personal user
        Then I should see a confirmation message

    @security @register
    Scenario: Create an pro user account
        Given I am not logged in
        And I register as pro user
        Then I should see a confirmation message

    @security @register-empty-form
    Scenario: Create an user account with empty values
        Given I am not logged in
        And I submit an empty form
        Then I should see error messages for all mandatory fields

    @security @register-as-an-existing-user
    Scenario: Register as an existing user
        Given I am not logged in
        And I register as an existing user
        Then I should see an error message

    @security @go-to-secure-page
    Scenario: Go to secure page url
        Given I am not logged in
        And I go to "/mon-profil"
        Then the url should match "connexion"
        When I fill the connexion form on dedicated page as user
        Then the url should match "/mon-profil"
