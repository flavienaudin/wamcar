Feature: Control form vehicle + personal user

    @register @register-with-wrong-immat
    Scenario: Register with wrong immat
        Given I am not logged in
        And I register with wrong plate
        And the url should match "/je-vends-mon-vehicule"
        Then I should see an error message

    @register @register-with-good-immat
    Scenario: Register with good immat
        Given I am not logged in
        And I register with good plate
        And the url should match "/je-vends-mon-vehicule"
        Then field 'make' is equals to 'VW'
        And field 'model' is equals to 'TIGUAN'
        And field 'modelVersion' is equals to 'TIGUAN 2.0 TDI 2008'
        And field 'engine' is equals to '2.0 TDI'
        And field 'transmission' is equals to 'MANUAL'
        And field 'fuel' is equals to 'Diesel'
